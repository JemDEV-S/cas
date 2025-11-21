<?php

namespace Modules\Configuration\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Configuration\Entities\SystemConfig;
use Modules\Configuration\Entities\ConfigGroup;

class ConfigRepository extends BaseRepository
{
    public function __construct(SystemConfig $model)
    {
        parent::__construct($model);
    }

    /**
     * Buscar configuración por clave
     */
    public function findByKey(string $key): ?SystemConfig
    {
        return $this->model->where('key', $key)->first();
    }

    /**
     * Obtener configuraciones por grupo
     */
    public function getByGroup(string $groupCode): \Illuminate\Support\Collection
    {
        return $this->model->byGroup($groupCode)->orderBy('display_order')->get();
    }

    /**
     * Obtener configuraciones públicas
     */
    public function getPublicConfigs(): \Illuminate\Support\Collection
    {
        return $this->model->public()->with('group')->orderBy('display_order')->get();
    }

    /**
     * Obtener configuraciones editables
     */
    public function getEditableConfigs(): \Illuminate\Support\Collection
    {
        return $this->model->editable()->with('group')->orderBy('display_order')->get();
    }

    /**
     * Obtener todas las configuraciones agrupadas
     */
    public function getAllGrouped(): \Illuminate\Support\Collection
    {
        $groups = ConfigGroup::active()->with('configs')->orderBy('order')->get();
        return $groups;
    }

    /**
     * Verificar si existe una clave
     */
    public function keyExists(string $key): bool
    {
        return $this->model->where('key', $key)->exists();
    }

    /**
     * Obtener historial de una configuración
     */
    public function getHistory(string $configId, int $limit = 50): \Illuminate\Support\Collection
    {
        $config = $this->find($configId);
        return $config->history()->with('changedBy')->limit($limit)->get();
    }
}
