<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Exceptions\UnauthorizedException;

/**
 * CheckRole Middleware
 *
 * Verifica que el usuario tenga uno de los roles requeridos.
 * Los super-admin tienen acceso automático a todo.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!$request->user()) {
            throw new UnauthorizedException('Usuario no autenticado.');
        }

        $user = $request->user();

        // Super-admin tiene acceso a todo
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $userRoles = $user->roles->pluck('slug')->toArray();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        throw new UnauthorizedException('No tienes permiso para acceder a este recurso.');
    }
}
