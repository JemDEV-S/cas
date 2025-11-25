<?php

namespace Modules\User\Policies;

use Modules\User\Entities\User;

class UserPolicy
{
    /**
     * Ver lista de usuarios
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.view.users');
    }

    /**
     * Ver un usuario específico
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        // Puede ver su propio perfil o si tiene permiso general
        return $currentUser->id === $targetUser->id
            || $currentUser->hasPermission('user.view.user');
    }

    /**
     * Crear usuario
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('user.create.user');
    }

    /**
     * Actualizar usuario
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        // Puede actualizar su propio perfil o si tiene permiso general
        if ($currentUser->id === $targetUser->id) {
            return $currentUser->hasPermission('user.update.own');
        }

        return $currentUser->hasPermission('user.update.user');
    }

    /**
     * Eliminar usuario
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        // No puede eliminarse a sí mismo
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        return $currentUser->hasPermission('user.delete.user');
    }

    /**
     * Cambiar estado del usuario
     */
    public function toggleStatus(User $currentUser, User $targetUser): bool
    {
        // No puede cambiar su propio estado
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        return $currentUser->hasPermission('user.toggle.status');
    }

    /**
     * Restablecer contraseña
     */
    public function resetPassword(User $currentUser, User $targetUser): bool
    {
        // No puede resetear su propia contraseña por esta vía
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        return $currentUser->hasPermission('user.reset.password');
    }

    /**
     * Gestionar preferencias
     */
    public function managePreferences(User $currentUser, User $targetUser): bool
    {
        // Siempre puede gestionar sus propias preferencias
        if ($currentUser->id === $targetUser->id) {
            return true;
        }

        return $currentUser->hasPermission('user.manage.preferences');
    }

    /**
     * Exportar usuarios
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('user.export.users');
    }
}
