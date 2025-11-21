<?php

namespace Modules\Auth\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Auth\Entities\Permission;

class PermissionRepository extends BaseRepository
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    public function existsBySlug(string $slug): bool
    {
        return $this->model->where('slug', $slug)->exists();
    }

    public function findBySlug(string $slug): ?Permission
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getByModule(string $module)
    {
        return $this->model->byModule($module)->get();
    }

    public function getActive()
    {
        return $this->model->where('is_active', true)->get();
    }
}
