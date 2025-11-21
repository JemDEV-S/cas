<?php

namespace Modules\Auth\Policies;

use Modules\Auth\Entities\Permission;

class PermissionPolicy
{
    public function viewAny($user): bool
    {
        return $this->hasPermission($user, 'auth.view.permissions');
    }

    public function view($user, Permission $permission): bool
    {
        return $this->hasPermission($user, 'auth.view.permission');
    }

    public function create($user): bool
    {
        return $this->hasPermission($user, 'auth.create.permission');
    }

    public function update($user, Permission $permission): bool
    {
        return $this->hasPermission($user, 'auth.update.permission');
    }

    public function delete($user, Permission $permission): bool
    {
        return $this->hasPermission($user, 'auth.delete.permission');
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
