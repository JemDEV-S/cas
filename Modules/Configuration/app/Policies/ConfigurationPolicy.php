<?php

namespace Modules\Configuration\Policies;

use Modules\User\Entities\User;
use Modules\Configuration\Entities\SystemConfig;
use Illuminate\Auth\Access\Response;

class ConfigurationPolicy
{
    /**
     * Determine if the user can view any configurations.
     */
    public function viewAny(User $user): Response
    {
        return $user->hasPermission('configuration.view.configs')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver las configuraciones del sistema. Contacta al administrador.');
    }

    /**
     * Determine if the user can view the configuration.
     */
    public function view(User $user, SystemConfig $config): Response
    {
        return $user->hasPermission('configuration.view.configs')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver esta configuración.');
    }

    /**
     * Determine if the user can update configurations.
     */
    public function update(User $user): Response
    {
        return $user->hasPermission('configuration.update.config')
            ? Response::allow()
            : Response::deny('No tienes permiso para actualizar configuraciones.');
    }

    /**
     * Determine if the user can view configuration history.
     */
    public function viewHistory(User $user): Response
    {
        return $user->hasPermission('configuration.view.history')
            ? Response::allow()
            : Response::deny('No tienes permiso para ver el historial de configuraciones.');
    }

    /**
     * Determine if the user can reset configurations.
     */
    public function reset(User $user): Response
    {
        return $user->hasPermission('configuration.update.config')
            ? Response::allow()
            : Response::deny('No tienes permiso para resetear configuraciones.');
    }
}
