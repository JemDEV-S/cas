<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Exceptions\UnauthorizedException;

/**
 * CheckPermission Middleware
 *
 * Verifica que el usuario tenga uno de los permisos requeridos.
 * Los super-admin tienen acceso automático a todo.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        if (!$request->user()) {
            throw new UnauthorizedException('Usuario no autenticado.');
        }

        $user = $request->user();

        // Super-admin tiene todos los permisos
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return $next($request);
            }
        }

        throw new UnauthorizedException('No tienes permiso para realizar esta acción.');
    }

    private function hasPermission($user, string $permissionSlug): bool
    {
        return $user->hasPermission($permissionSlug);
    }
}
