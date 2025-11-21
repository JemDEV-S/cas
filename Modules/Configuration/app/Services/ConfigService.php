<?php

namespace Modules\Configuration\Services;

use Modules\Core\Services\BaseService;
use Modules\Configuration\Repositories\ConfigRepository;
use Modules\Configuration\Entities\SystemConfig;
use Modules\Configuration\Entities\ConfigHistory;
use Modules\Core\Exceptions\ValidationException;
use Modules\Core\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ConfigService extends BaseService
{
    protected const CACHE_PREFIX = 'config:';
    protected const CACHE_TTL = 3600; // 1 hora por defecto
    protected const CACHE_KEY_ALL = 'config:all';

    public function __construct(
        protected ConfigRepository $repository
    ) {
        parent::__construct($repository);
    }

    /**
     * Obtener valor de configuración por clave
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $config = $this->repository->findByKey($key);

            if (!$config) {
                return $default;
            }

            return $config->parsed_value;
        });
    }

    /**
     * Establecer valor de configuración
     */
    public function set(string $key, $value, ?string $changedBy = null, ?string $reason = null): SystemConfig
    {
        $config = $this->repository->findByKey($key);

        if (!$config) {
            throw new BusinessRuleException("Configuración '{$key}' no existe");
        }

        if (!$config->is_editable) {
            throw new BusinessRuleException("Configuración '{$key}' no es editable");
        }

        // Validar el nuevo valor
        $this->validateValue($config, $value);

        // Guardar en historial
        $oldValue = $config->value;
        $newValue = $this->castValueToString($value, $config);

        if ($oldValue !== $newValue) {
            $this->saveHistory($config, $oldValue, $newValue, $changedBy, $reason);

            // Actualizar el valor
            $config->value = $newValue;
            $config->save();

            // Limpiar caché
            $this->clearCache($key);

            // Disparar evento
            event(new \Modules\Configuration\Events\ConfigUpdated($config, $changedBy));
        }

        return $config;
    }

    /**
     * Verificar si existe una configuración
     */
    public function has(string $key): bool
    {
        return $this->repository->keyExists($key);
    }

    /**
     * Obtener todas las configuraciones de un grupo
     */
    public function group(string $groupCode): array
    {
        $configs = $this->repository->getByGroup($groupCode);

        $result = [];
        foreach ($configs as $config) {
            $result[$config->key] = $config->parsed_value;
        }

        return $result;
    }

    /**
     * Obtener todas las configuraciones
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            $configs = $this->repository->all();

            $result = [];
            foreach ($configs as $config) {
                $result[$config->key] = $config->parsed_value;
            }

            return $result;
        });
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public function updateBatch(array $configs, ?string $changedBy = null, ?string $reason = null): array
    {
        $updated = [];

        foreach ($configs as $key => $value) {
            try {
                $updated[$key] = $this->set($key, $value, $changedBy, $reason);
            } catch (\Exception $e) {
                // Registrar el error pero continuar con las demás
                \Log::error("Error updating config {$key}: " . $e->getMessage());
            }
        }

        return $updated;
    }

    /**
     * Restablecer configuración a su valor por defecto
     */
    public function reset(string $key, ?string $changedBy = null, ?string $reason = null): SystemConfig
    {
        $config = $this->repository->findByKey($key);

        if (!$config) {
            throw new BusinessRuleException("Configuración '{$key}' no existe");
        }

        return $this->set($key, $config->default_value, $changedBy, $reason ?? 'Resetear a valor por defecto');
    }

    /**
     * Limpiar toda la caché de configuración
     */
    public function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        } else {
            Cache::forget(self::CACHE_KEY_ALL);
            // También limpiar todas las claves individuales conocidas
            $configs = $this->repository->all();
            foreach ($configs as $config) {
                Cache::forget(self::CACHE_PREFIX . $config->key);
            }

            event(new \Modules\Configuration\Events\ConfigCacheCleared());
        }
    }

    /**
     * Validar el valor según las reglas de configuración
     */
    protected function validateValue(SystemConfig $config, $value): void
    {
        $rules = $config->validation_rules ?? [];

        if (!empty($rules)) {
            $validator = Validator::make(
                ['value' => $value],
                ['value' => $rules]
            );

            if ($validator->fails()) {
                throw new ValidationException($validator->errors()->first('value'));
            }
        }

        // Validaciones adicionales según el tipo
        if ($config->min_value !== null && is_numeric($value)) {
            if ($value < $config->min_value) {
                throw new ValidationException("El valor debe ser mayor o igual a {$config->min_value}");
            }
        }

        if ($config->max_value !== null && is_numeric($value)) {
            if ($value > $config->max_value) {
                throw new ValidationException("El valor debe ser menor o igual a {$config->max_value}");
            }
        }

        // Validar opciones si es select
        if (!empty($config->options) && is_array($config->options)) {
            if (!in_array($value, $config->options)) {
                throw new ValidationException("El valor debe ser una de las opciones permitidas");
            }
        }
    }

    /**
     * Convertir el valor a string para almacenamiento
     */
    protected function castValueToString($value, SystemConfig $config): string
    {
        return match ($config->value_type->value) {
            'boolean' => $value ? '1' : '0',
            'json' => is_string($value) ? $value : json_encode($value),
            'date', 'datetime' => $value instanceof \Carbon\Carbon ? $value->toDateTimeString() : $value,
            default => (string) $value,
        };
    }

    /**
     * Guardar en historial
     */
    protected function saveHistory(
        SystemConfig $config,
        ?string $oldValue,
        ?string $newValue,
        ?string $changedBy,
        ?string $reason
    ): void {
        ConfigHistory::create([
            'system_config_id' => $config->id,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $changedBy,
            'changed_at' => now(),
            'change_reason' => $reason,
            'ip_address' => request()?->ip(),
        ]);

        // Si es configuración crítica, disparar evento
        if ($config->is_system) {
            event(new \Modules\Configuration\Events\CriticalConfigChanged($config, $changedBy));
        }
    }

    /**
     * Obtener historial de cambios de una configuración
     */
    public function getHistory(string $key, int $limit = 50): \Illuminate\Support\Collection
    {
        $config = $this->repository->findByKey($key);

        if (!$config) {
            throw new BusinessRuleException("Configuración '{$key}' no existe");
        }

        return $this->repository->getHistory($config->id, $limit);
    }
}
