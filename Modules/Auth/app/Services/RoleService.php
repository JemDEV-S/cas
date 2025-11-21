<?php

namespace Modules\Auth\Services;

use Modules\Core\Services\BaseService;
use Modules\Auth\Entities\Role;
use Modules\Auth\Repositories\RoleRepository;
use Modules\Core\Exceptions\BusinessRuleException;
use Illuminate\Support\Str;

class RoleService extends BaseService
{
    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Role
    {
        $data['slug'] = Str::slug($data['name']);

        if ($this->repository->existsBySlug($data['slug'])) {
            throw new BusinessRuleException('Ya existe un rol con ese nombre.');
        }

        return $this->repository->create($data);
    }

    public function update(string $id, array $data): Role
    {
        $role = $this->repository->findOrFail($id);

        if ($role->is_system) {
            throw new BusinessRuleException('No se puede modificar un rol del sistema.');
        }

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $this->repository->update($id, $data);

        return $this->repository->findOrFail($id);
    }

    public function delete(string $id): void
    {
        $role = $this->repository->findOrFail($id);

        if ($role->is_system) {
            throw new BusinessRuleException('No se puede eliminar un rol del sistema.');
        }

        $this->repository->delete($id);
    }

    public function assignPermissions(string $roleId, array $permissionIds): void
    {
        $role = $this->repository->findOrFail($roleId);
        $role->syncPermissions($permissionIds);
    }
}
