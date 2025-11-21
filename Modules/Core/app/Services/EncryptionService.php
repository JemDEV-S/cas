<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Crypt;

/**
 * Encryption Service
 *
 * Servicio para encriptación de datos sensibles.
 */
class EncryptionService
{
    /**
     * Encripta un valor.
     *
     * @param mixed $value
     * @return string
     */
    public function encrypt($value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Desencripta un valor.
     *
     * @param string $encryptedValue
     * @return mixed
     */
    public function decrypt(string $encryptedValue)
    {
        try {
            return Crypt::decryptString($encryptedValue);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Hashea un valor.
     *
     * @param string $value
     * @return string
     */
    public function hash(string $value): string
    {
        return bcrypt($value);
    }

    /**
     * Verifica un hash.
     *
     * @param string $value
     * @param string $hashedValue
     * @return bool
     */
    public function verifyHash(string $value, string $hashedValue): bool
    {
        return \Hash::check($value, $hashedValue);
    }

    /**
     * Genera un token aleatorio.
     *
     * @param int $length
     * @return string
     */
    public function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Genera un código aleatorio numérico.
     *
     * @param int $length
     * @return string
     */
    public function generateNumericCode(int $length = 6): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }
}
