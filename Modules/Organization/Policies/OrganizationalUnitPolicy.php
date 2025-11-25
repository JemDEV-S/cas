<?php

namespace Modules\Organization\Policies;

use Modules\User\Entities\User;
use Modules\Organization\Entities\OrganizationalUnit;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationalUnitPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver cualquier unidad organizacional
     */
    public function viewAny(User $user): bool
    {
        return $user->can('organization.view.units');
    }

    /**
     * Determinar si el usuario puede ver una unidad organizacional específica
     */
    public function view(User $user, OrganizationalUnit $organizationalUnit): bool
    {
        return $user->can('organization.view.unit');
    }

    /**
     * Determinar si el usuario puede crear unidades organizacionales
     */
    public function create(User $user): bool
    {
        return $user->can('organization.create.unit');
    }

    /**
     * Determinar si el usuario puede actualizar una unidad organizacional
     */
    public function update(User $user, OrganizationalUnit $organizationalUnit): bool
    {
        // Solo usuarios con permiso pueden actualizar
        if (!$user->can('organization.update.unit')) {
            return false;
        }

        // No se puede actualizar una unidad eliminada
        if ($organizationalUnit->trashed()) {
            return false;
        }

        return true;
    }

    /**
     * Determinar si el usuario puede eliminar una unidad organizacional
     */
    public function delete(User $user, OrganizationalUnit $organizationalUnit): bool
    {
        // Solo usuarios con permiso pueden eliminar
        if (!$user->can('organization.delete.unit')) {
            return false;
        }

        // No se puede eliminar una unidad con hijos
        if ($organizationalUnit->hasChildren()) {
            return false;
        }

        return true;
    }

    /**
     * Determinar si el usuario puede restaurar una unidad organizacional eliminada
     */
    public function restore(User $user, OrganizationalUnit $organizationalUnit): bool
    {
        return $user->can('organization.restore.unit');
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente una unidad organizacional
     */
    public function forceDelete(User $user, OrganizationalUnit $organizationalUnit): bool
    {
        return $user->can('organization.force-delete.unit');
    }

    /**
     * Determinar si el usuario puede ver el árbol organizacional
     */
    public function viewTree(User $user): bool
    {
        return $user->can('organization.view.tree');
    }

    /**
     * Determinar si el usuario puede mover unidades en la jerarquía
     */
    public function move(User $user, OrganizationalUnit $organizationalUnit): bool
    {
        // Solo usuarios con permiso pueden mover
        if (!$user->can('organization.move.unit')) {
            return false;
        }

        // No se puede mover una unidad con hijos
        if ($organizationalUnit->hasChildren()) {
            return false;
        }

        return true;
    }

    /**
     * Determinar si el usuario puede exportar datos organizacionales
     */
    public function export(User $user): bool
    {
        return $user->can('organization.export.data');
    }

    /**
     * Determinar si el usuario puede gestionar la jerarquía (reconstruir closure table)
     */
    public function manageHierarchy(User $user): bool
    {
        return $user->can('organization.manage.hierarchy');
    }
}
