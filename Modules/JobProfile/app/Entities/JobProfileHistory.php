<?php

namespace Modules\JobProfile\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobProfileHistory extends Model
{
    use HasUuid;

    protected $table = 'job_profile_history';

    protected $fillable = [
        'job_profile_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'description',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $searchable = ['action', 'description'];
    protected $sortable = ['created_at', 'action'];

    // Relaciones
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class);
    }

    // Scopes
    public function scopeByJobProfile($query, string $jobProfileId)
    {
        return $query->where('job_profile_id', $jobProfileId);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeStatusChanges($query)
    {
        return $query->whereNotNull('from_status')
            ->whereNotNull('to_status');
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'submitted' => 'Enviado a revisión',
            'in_review' => 'En revisión',
            'modification_requested' => 'Modificación solicitada',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            'activated' => 'Activado',
            'deactivated' => 'Desactivado',
            'deleted' => 'Eliminado',
            default => ucfirst($this->action),
        };
    }

    public function getStatusChangeTextAttribute(): ?string
    {
        if ($this->from_status && $this->to_status) {
            $fromLabel = \Modules\JobProfile\Enums\JobProfileStatusEnum::from($this->from_status)->label();
            $toLabel = \Modules\JobProfile\Enums\JobProfileStatusEnum::from($this->to_status)->label();
            return "{$fromLabel} → {$toLabel}";
        }

        return null;
    }

    /**
     * Crea un registro de historial
     */
    public static function log(
        string $jobProfileId,
        string $action,
        ?string $userId = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $description = null,
        ?array $changes = null
    ): self {
        return static::create([
            'job_profile_id' => $jobProfileId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
        ]);
    }
}
