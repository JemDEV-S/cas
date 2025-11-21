<?php

namespace Modules\Auth\Services;

use Modules\Core\Services\BaseService;
use Modules\Auth\Entities\LoginAttempt;
use Modules\Auth\Entities\UserSession;
use Modules\Core\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService extends BaseService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 15; // minutos

    public function login(string $email, string $password, string $ip, string $userAgent): array
    {
        $this->checkLoginAttempts($email, $ip);

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            $this->recordLoginAttempt($email, $ip, $userAgent, false);
            throw new BusinessRuleException('Credenciales inválidas.');
        }

        if (!$user->is_active) {
            throw new BusinessRuleException('Usuario inactivo.');
        }

        $this->recordLoginAttempt($email, $ip, $userAgent, true);
        $session = $this->createSession($user->id, $ip, $userAgent);

        return [
            'user' => $user,
            'token' => $session->token,
            'expires_at' => $session->expires_at,
        ];
    }

    public function logout(string $token): void
    {
        UserSession::where('token', $token)->delete();
    }

    public function validateSession(string $token): ?object
    {
        $session = UserSession::where('token', $token)->active()->first();

        if (!$session) {
            return null;
        }

        $session->updateActivity();

        return (object)[
            'user_id' => $session->user_id,
            'session' => $session,
        ];
    }

    private function createSession(string $userId, string $ip, string $userAgent): UserSession
    {
        return UserSession::create([
            'user_id' => $userId,
            'token' => Str::random(64),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'last_activity' => now(),
            'expires_at' => now()->addHours(12),
        ]);
    }

    private function checkLoginAttempts(string $email, string $ip): void
    {
        $attempts = LoginAttempt::recentByEmail($email, self::LOCKOUT_TIME)
            ->failed()
            ->count();

        if ($attempts >= self::MAX_ATTEMPTS) {
            throw new BusinessRuleException('Demasiados intentos fallidos. Intenta nuevamente en ' . self::LOCKOUT_TIME . ' minutos.');
        }
    }

    private function recordLoginAttempt(string $email, string $ip, string $userAgent, bool $successful): void
    {
        LoginAttempt::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'successful' => $successful,
            'attempted_at' => now(),
        ]);
    }
}
