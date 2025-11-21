<?php

namespace Modules\Auth\Services;

use Modules\Core\Services\BaseService;
use Modules\Auth\Entities\Permission;
use Modules\Auth\Repositories\PermissionRepository;
use Modules\Core\Exceptions\BusinessRuleException;
use Illuminate\Support\Str;

class PermissionService extends BaseService
{
    public function __construct(PermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Permission
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($this->repository->existsBySlug($data['slug'])) {
            throw new BusinessRuleException('Ya existe un permiso con ese nombre.');
        }

        return $this->repository->create($data);
    }

    public function update(string $id, array $data): Permission
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $this->repository->update($id, $data);

        return $this->repository->findOrFail($id);
    }

    public function delete(string $id): void
    {
        $this->repository->delete($id);
    }

    public function getByModule(string $module): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getByModule($module);
    }
}
