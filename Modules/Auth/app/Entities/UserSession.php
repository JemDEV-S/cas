<?php

namespace Modules\Auth\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\HasUuid;

/**
 * UserSession Entity
 *
 * Representa una sesión activa de usuario.
 */
class UserSession extends BaseModel
{
    use HasUuid;

    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'token',
        'ip_address',
        'user_agent',
        'last_activity',
        'expires_at',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Verifica si la sesión está expirada.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verifica si la sesión está activa.
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Actualiza la última actividad de la sesión.
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Scope para sesiones activas.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope para sesiones expiradas.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
