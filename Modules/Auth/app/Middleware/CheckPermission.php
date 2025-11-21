<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Exceptions\UnauthorizedException;

/**
 * CheckPermission Middleware
 *
 * Verifica que el usuario tenga uno de los permisos requeridos.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        if (!$request->user()) {
            throw new UnauthorizedException('Usuario no autenticado.');
        }

        $user = $request->user();

        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return $next($request);
            }
        }

        throw new UnauthorizedException('No tienes permiso para realizar esta acción.');
    }

    private function hasPermission($user, string $permissionSlug): bool
    {
        foreach ($user->roles as $role) {
            if ($role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }
}
