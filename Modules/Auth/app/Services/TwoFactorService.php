<?php

namespace Modules\Auth\Services;

use Modules\User\Entities\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TwoFactorService
{
    protected const CODE_LENGTH = 6;
    protected const CODE_EXPIRY = 300; // 5 minutos
    protected const CACHE_PREFIX = '2fa:';
    protected const MAX_ATTEMPTS = 3;

    /**
     * Generar código de verificación
     */
    public function generateCode(User $user): string
    {
        $code = $this->generateRandomCode();

        // Guardar en caché
        $cacheKey = $this->getCacheKey($user->id);
        Cache::put($cacheKey, [
            'code' => $code,
            'attempts' => 0,
            'generated_at' => now()->timestamp,
        ], self::CODE_EXPIRY);

        return $code;
    }

    /**
     * Verificar código
     */
    public function verifyCode(User $user, string $code): bool
    {
        $cacheKey = $this->getCacheKey($user->id);
        $data = Cache::get($cacheKey);

        if (!$data) {
            return false;
        }

        // Verificar intentos
        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($cacheKey);
            return false;
        }

        // Incrementar intentos
        $data['attempts']++;
        Cache::put($cacheKey, $data, self::CODE_EXPIRY);

        // Verificar código
        if ($data['code'] === $code) {
            Cache::forget($cacheKey);
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene 2FA habilitado
     */
    public function isEnabled(User $user): bool
    {
        return $user->two_factor_enabled ?? false;
    }

    /**
     * Habilitar 2FA para un usuario
     */
    public function enable(User $user): bool
    {
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $this->generateSecret(),
        ]);

        return true;
    }

    /**
     * Deshabilitar 2FA para un usuario
     */
    public function disable(User $user): bool
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        return true;
    }

    /**
     * Obtener intentos restantes
     */
    public function getRemainingAttempts(User $user): int
    {
        $cacheKey = $this->getCacheKey($user->id);
        $data = Cache::get($cacheKey);

        if (!$data) {
            return self::MAX_ATTEMPTS;
        }

        return max(0, self::MAX_ATTEMPTS - $data['attempts']);
    }

    /**
     * Verificar si el código ha expirado
     */
    public function isCodeExpired(User $user): bool
    {
        $cacheKey = $this->getCacheKey($user->id);
        return !Cache::has($cacheKey);
    }

    /**
     * Limpiar código
     */
    public function clearCode(User $user): void
    {
        $cacheKey = $this->getCacheKey($user->id);
        Cache::forget($cacheKey);
    }

    /**
     * Generar código aleatorio
     */
    protected function generateRandomCode(): string
    {
        return str_pad(
            (string) random_int(0, pow(10, self::CODE_LENGTH) - 1),
            self::CODE_LENGTH,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generar secreto
     */
    protected function generateSecret(): string
    {
        return Str::random(32);
    }

    /**
     * Obtener clave de caché
     */
    protected function getCacheKey(string $userId): string
    {
        return self::CACHE_PREFIX . $userId;
    }

    /**
     * Enviar código por email
     */
    public function sendCodeByEmail(User $user): bool
    {
        $code = $this->generateCode($user);

        // Aquí se enviaría el email con el código
        // Implementar con el módulo de Notification cuando esté disponible

        \Log::info('2FA code generated', [
            'user_id' => $user->id,
            'code' => $code, // En producción, NO loguear el código
        ]);

        return true;
    }

    /**
     * Enviar código por SMS
     */
    public function sendCodeBySMS(User $user): bool
    {
        $code = $this->generateCode($user);

        // Aquí se enviaría el SMS con el código
        // Implementar con servicio de SMS

        \Log::info('2FA SMS sent', [
            'user_id' => $user->id,
            'phone' => $user->phone,
        ]);

        return true;
    }

    /**
     * Generar códigos de recuperación
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::random(10);
        }

        return $codes;
    }

    /**
     * Guardar códigos de recuperación
     */
    public function saveRecoveryCodes(User $user, array $codes): bool
    {
        $hashedCodes = array_map(fn($code) => hash('sha256', $code), $codes);

        $user->update([
            'two_factor_recovery_codes' => json_encode($hashedCodes),
        ]);

        return true;
    }

    /**
     * Verificar código de recuperación
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);

        if (empty($recoveryCodes)) {
            return false;
        }

        $hashedCode = hash('sha256', $code);

        if (in_array($hashedCode, $recoveryCodes, true)) {
            // Remover el código usado
            $recoveryCodes = array_filter($recoveryCodes, fn($c) => $c !== $hashedCode);

            $user->update([
                'two_factor_recovery_codes' => json_encode(array_values($recoveryCodes)),
            ]);

            return true;
        }

        return false;
    }
}
