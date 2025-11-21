<?php

namespace Modules\Auth\Policies;

use Modules\Auth\Entities\Role;

class RolePolicy
{
    public function viewAny($user): bool
    {
        return $this->hasPermission($user, 'auth.view.roles');
    }

    public function view($user, Role $role): bool
    {
        return $this->hasPermission($user, 'auth.view.role');
    }

    public function create($user): bool
    {
        return $this->hasPermission($user, 'auth.create.role');
    }

    public function update($user, Role $role): bool
    {
        return !$role->is_system && $this->hasPermission($user, 'auth.update.role');
    }

    public function delete($user, Role $role): bool
    {
        return !$role->is_system && $this->hasPermission($user, 'auth.delete.role');
    }

    private function hasPermission($user, string $permission): bool
    {
        foreach ($user->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
}
