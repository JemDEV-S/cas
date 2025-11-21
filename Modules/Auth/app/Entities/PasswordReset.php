<?php

namespace Modules\Auth\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;

/**
 * PasswordReset Entity
 *
 * Representa un token de recuperación de contraseña.
 */
class PasswordReset extends BaseModel
{
    use HasUuid;

    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Verifica si el token está expirado.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verifica si el token ya fue usado.
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Verifica si el token es válido.
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }

    /**
     * Marca el token como usado.
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Scope para tokens válidos.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
            ->whereNull('used_at');
    }

    /**
     * Scope para tokens expirados.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
