<?php

namespace Modules\Auth\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;

/**
 * LoginAttempt Entity
 *
 * Representa un intento de inicio de sesión.
 */
class LoginAttempt extends BaseModel
{
    use HasUuid;

    protected $table = 'login_attempts';

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'attempted_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'attempted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope para intentos exitosos.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    /**
     * Scope para intentos fallidos.
     */
    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    /**
     * Scope para intentos recientes por IP.
     */
    public function scopeRecentByIp($query, string $ip, int $minutes = 15)
    {
        return $query->where('ip_address', $ip)
            ->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope para intentos recientes por email.
     */
    public function scopeRecentByEmail($query, string $email, int $minutes = 15)
    {
        return $query->where('email', $email)
            ->where('attempted_at', '>=', now()->subMinutes($minutes));
    }
}
