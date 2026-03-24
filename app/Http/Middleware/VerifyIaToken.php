<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIaToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $expectedToken = config('services.ia_agent.token');

        if (!$token || !$expectedToken || !hash_equals($expectedToken, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticación inválido',
            ], 401);
        }

        return $next($request);
    }
}
