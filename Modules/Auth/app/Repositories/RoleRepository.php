<?php

namespace Modules\Auth\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Auth\Entities\Role;

class RoleRepository extends BaseRepository
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function existsBySlug(string $slug): bool
    {
        return $this->model->where('slug', $slug)->exists();
    }

    public function findBySlug(string $slug): ?Role
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getActive()
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getSystemRoles()
    {
        return $this->model->where('is_system', true)->get();
    }
}
