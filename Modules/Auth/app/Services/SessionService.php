<?php

namespace Modules\Auth\Services;

use Modules\Auth\Entities\UserSession;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SessionService
{
    /**
     * Crear una nueva sesión de usuario
     */
    public function create(User $user, string $token, array $data = []): UserSession
    {
        return UserSession::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'last_activity' => now(),
            'expires_at' => now()->addMinutes(config('session.lifetime', 120)),
            'metadata' => array_merge($data, [
                'login_at' => now()->toIso8601String(),
                'device' => $this->getDeviceInfo(),
            ]),
        ]);
    }

    /**
     * Actualizar última actividad de la sesión
     */
    public function updateActivity(string $token): ?UserSession
    {
        $session = $this->findByToken($token);

        if ($session) {
            $session->update([
                'last_activity' => now(),
            ]);
        }

        return $session;
    }

    /**
     * Encontrar sesión por token
     */
    public function findByToken(string $token): ?UserSession
    {
        return UserSession::where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Invalidar una sesión
     */
    public function invalidate(string $token): bool
    {
        return UserSession::where('token', hash('sha256', $token))
            ->delete();
    }

    /**
     * Invalidar todas las sesiones de un usuario
     */
    public function invalidateAllForUser(User $user, ?string $exceptToken = null): int
    {
        $query = UserSession::where('user_id', $user->id);

        if ($exceptToken) {
            $query->where('token', '!=', hash('sha256', $exceptToken));
        }

        return $query->delete();
    }

    /**
     * Obtener sesiones activas de un usuario
     */
    public function getActiveSessions(User $user): \Illuminate\Support\Collection
    {
        return UserSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Limpiar sesiones expiradas
     */
    public function cleanExpired(): int
    {
        return UserSession::where('expires_at', '<', now())
            ->delete();
    }

    /**
     * Verificar si un usuario tiene sesiones activas
     */
    public function hasActiveSessions(User $user): bool
    {
        return UserSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Contar sesiones activas del usuario
     */
    public function countActiveSessions(User $user): int
    {
        return UserSession::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->count();
    }

    /**
     * Verificar límite de sesiones concurrentes
     */
    public function checkConcurrentSessionsLimit(User $user, int $limit = 5): bool
    {
        return $this->countActiveSessions($user) < $limit;
    }

    /**
     * Obtener información del dispositivo
     */
    protected function getDeviceInfo(): array
    {
        $userAgent = request()->userAgent();

        return [
            'platform' => $this->detectPlatform($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'is_mobile' => $this->isMobile($userAgent),
        ];
    }

    /**
     * Detectar plataforma
     */
    protected function detectPlatform(string $userAgent): string
    {
        if (stripos($userAgent, 'Windows') !== false) return 'Windows';
        if (stripos($userAgent, 'Mac') !== false) return 'Mac';
        if (stripos($userAgent, 'Linux') !== false) return 'Linux';
        if (stripos($userAgent, 'Android') !== false) return 'Android';
        if (stripos($userAgent, 'iOS') !== false || stripos($userAgent, 'iPhone') !== false) return 'iOS';

        return 'Unknown';
    }

    /**
     * Detectar navegador
     */
    protected function detectBrowser(string $userAgent): string
    {
        if (stripos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (stripos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (stripos($userAgent, 'Safari') !== false) return 'Safari';
        if (stripos($userAgent, 'Edge') !== false) return 'Edge';
        if (stripos($userAgent, 'Opera') !== false) return 'Opera';

        return 'Unknown';
    }

    /**
     * Verificar si es dispositivo móvil
     */
    protected function isMobile(string $userAgent): bool
    {
        return preg_match('/Mobile|Android|iPhone|iPad/', $userAgent) === 1;
    }

    /**
     * Obtener estadísticas de sesiones
     */
    public function getStatistics(): array
    {
        return [
            'total_active' => UserSession::where('expires_at', '>', now())->count(),
            'total_today' => UserSession::whereDate('created_at', today())->count(),
            'unique_users_today' => UserSession::whereDate('created_at', today())
                ->distinct('user_id')
                ->count('user_id'),
            'by_platform' => UserSession::where('expires_at', '>', now())
                ->select(DB::raw("metadata->>'device.platform' as platform"), DB::raw('count(*) as count'))
                ->groupBy('platform')
                ->pluck('count', 'platform')
                ->toArray(),
        ];
    }
}
