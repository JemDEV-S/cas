<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Services\TwoFactorService;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuth
{
    public function __construct(
        protected TwoFactorService $twoFactorService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Si el usuario tiene 2FA habilitado y no ha verificado en esta sesión
        if ($this->twoFactorService->isEnabled($user)) {
            if (!$request->session()->get('2fa_verified', false)) {
                return redirect()->route('auth.2fa.verify');
            }
        }

        return $next($request);
    }
}
