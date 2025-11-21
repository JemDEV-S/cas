<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Entities\LoginAttempt;

/**
 * TrackLoginAttempt Middleware
 *
 * Rastrea intentos de inicio de sesión.
 */
class TrackLoginAttempt
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('api/auth/login') || $request->is('login')) {
            $this->trackAttempt($request, $response->getStatusCode() === 200);
        }

        return $response;
    }

    private function trackAttempt(Request $request, bool $successful): void
    {
        LoginAttempt::create([
            'email' => $request->input('email', 'unknown'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $successful,
            'attempted_at' => now(),
        ]);
    }
}
