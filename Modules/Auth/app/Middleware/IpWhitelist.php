<?php

namespace Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = config('auth.ip_whitelist', []);

        // Si no hay whitelist configurada, permitir acceso
        if (empty($whitelist)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        // Verificar si la IP está en la whitelist
        if (!$this->isIpWhitelisted($clientIp, $whitelist)) {
            abort(403, 'Acceso denegado desde esta dirección IP.');
        }

        return $next($request);
    }

    /**
     * Verificar si la IP está en la whitelist
     */
    protected function isIpWhitelisted(string $ip, array $whitelist): bool
    {
        foreach ($whitelist as $allowedIp) {
            // Soportar rangos CIDR
            if ($this->matchCIDR($ip, $allowedIp)) {
                return true;
            }

            // Coincidencia exacta
            if ($ip === $allowedIp) {
                return true;
            }

            // Soportar wildcards
            if ($this->matchWildcard($ip, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar coincidencia CIDR
     */
    protected function matchCIDR(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return false;
        }

        list($subnet, $mask) = explode('/', $range);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - (int)$mask);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        // IPv6 no implementado en este ejemplo básico
        return false;
    }

    /**
     * Verificar coincidencia con wildcard
     */
    protected function matchWildcard(string $ip, string $pattern): bool
    {
        $pattern = str_replace('.', '\.', $pattern);
        $pattern = str_replace('*', '\d+', $pattern);
        $pattern = '/^' . $pattern . '$/';

        return preg_match($pattern, $ip) === 1;
    }
}
