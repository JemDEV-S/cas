<?php

namespace Modules\Configuration\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Configuration\Repositories\ConfigRepository;

class CacheService
{
    protected const CACHE_PREFIX = 'config:';
    protected const CACHE_TTL = 3600;

    public function __construct(
        protected ConfigRepository $repository
    ) {}

    /**
     * Recordar un valor en caché
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $cacheKey = $this->getCacheKey($key);
        $ttl = $ttl ?? self::CACHE_TTL;

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Obtener de caché
     */
    public function get(string $key, $default = null)
    {
        return Cache::get($this->getCacheKey($key), $default);
    }

    /**
     * Guardar en caché
     */
    public function put(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? self::CACHE_TTL;
        return Cache::put($this->getCacheKey($key), $value, $ttl);
    }

    /**
     * Verificar si existe en caché
     */
    public function has(string $key): bool
    {
        return Cache::has($this->getCacheKey($key));
    }

    /**
     * Olvidar una clave de caché
     */
    public function forget(string $key): bool
    {
        return Cache::forget($this->getCacheKey($key));
    }

    /**
     * Limpiar toda la caché de configuración
     */
    public function flush(): void
    {
        // Limpiar todas las configuraciones conocidas
        $configs = $this->repository->all();

        foreach ($configs as $config) {
            $this->forget($config->key);
        }

        // Limpiar caché de todos los grupos
        Cache::forget(self::CACHE_PREFIX . 'all');
        Cache::forget(self::CACHE_PREFIX . 'groups');
    }

    /**
     * Limpiar caché de un grupo
     */
    public function flushGroup(string $groupCode): void
    {
        $configs = $this->repository->getByGroup($groupCode);

        foreach ($configs as $config) {
            $this->forget($config->key);
        }

        Cache::forget(self::CACHE_PREFIX . "group:{$groupCode}");
    }

    /**
     * Obtener la clave de caché completa
     */
    protected function getCacheKey(string $key): string
    {
        return self::CACHE_PREFIX . $key;
    }

    /**
     * Obtener el TTL por defecto
     */
    public function getDefaultTtl(): int
    {
        return self::CACHE_TTL;
    }

    /**
     * Establecer TTL personalizado para una configuración crítica
     */
    public function getCacheTtlForConfig(string $key): int
    {
        $config = $this->repository->findByKey($key);

        if (!$config) {
            return self::CACHE_TTL;
        }

        // Configuraciones del sistema tienen TTL más largo
        if ($config->is_system) {
            return 7200; // 2 horas
        }

        // Configuraciones frecuentemente consultadas tienen TTL más corto
        return self::CACHE_TTL;
    }
}
