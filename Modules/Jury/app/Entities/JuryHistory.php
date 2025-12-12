<?php

namespace Modules\Jury\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Str;

class JuryHistory extends Model
{
    use HasFactory;

    protected $table = 'jury_history';

    public $timestamps = false;

    protected $fillable = [
        'jury_assignment_id',
        'jury_member_id',
        'job_posting_id',
        'event_type',
        'description',
        'reason',
        'old_values',
        'new_values',
        'old_status',
        'new_status',
        'related_jury_member_id',
        'performed_by',
        'performed_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->performed_at)) {
                $model->performed_at = now();
            }

            // Capturar IP y User Agent si no están establecidos
            if (empty($model->ip_address)) {
                $model->ip_address = request()->ip();
            }

            if (empty($model->user_agent)) {
                $model->user_agent = request()->userAgent();
            }
        });
    }

    /**
     * Relaciones
     */
    public function juryAssignment(): BelongsTo
    {
        return $this->belongsTo(JuryAssignment::class);
    }

    public function juryMember(): BelongsTo
    {
        return $this->belongsTo(JuryMember::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'performed_by');
    }

    public function relatedJuryMember(): BelongsTo
    {
        return $this->belongsTo(JuryMember::class, 'related_jury_member_id');
    }

    /**
     * Scopes
     */
    public function scopeByJuryMember($query, string $juryMemberId)
    {
        return $query->where('jury_member_id', $juryMemberId);
    }

    public function scopeByAssignment($query, string $assignmentId)
    {
        return $query->where('jury_assignment_id', $assignmentId);
    }

    public function scopeByJobPosting($query, string $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('performed_at', 'desc');
    }

    /**
     * Helper Methods
     */
    public static function log(array $data): self
    {
        return static::create($data);
    }

    public static function logAssignment(
        string $assignmentId,
        string $juryMemberId,
        string $jobPostingId,
        ?string $performedBy = null
    ): self {
        return static::log([
            'jury_assignment_id' => $assignmentId,
            'jury_member_id' => $juryMemberId,
            'job_posting_id' => $jobPostingId,
            'event_type' => 'ASSIGNED',
            'description' => 'Jurado asignado a la convocatoria',
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }

    public static function logReplacement(
        string $assignmentId,
        string $oldJuryMemberId,
        string $newJuryMemberId,
        string $reason,
        ?string $performedBy = null
    ): self {
        return static::log([
            'jury_assignment_id' => $assignmentId,
            'jury_member_id' => $oldJuryMemberId,
            'related_jury_member_id' => $newJuryMemberId,
            'event_type' => 'REPLACED',
            'description' => 'Jurado reemplazado',
            'reason' => $reason,
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }

    public static function logStatusChange(
        string $assignmentId,
        string $juryMemberId,
        string $oldStatus,
        string $newStatus,
        ?string $reason = null,
        ?string $performedBy = null
    ): self {
        return static::log([
            'jury_assignment_id' => $assignmentId,
            'jury_member_id' => $juryMemberId,
            'event_type' => 'STATUS_CHANGED',
            'description' => "Estado cambiado de {$oldStatus} a {$newStatus}",
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }

    public static function logTrainingCompleted(
        string $juryMemberId,
        ?string $performedBy = null
    ): self {
        return static::log([
            'jury_member_id' => $juryMemberId,
            'event_type' => 'TRAINING_COMPLETED',
            'description' => 'Capacitación de jurado completada',
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }

    public static function logConflictReported(
        string $juryMemberId,
        string $conflictType,
        ?string $applicationId = null,
        ?string $jobPostingId = null,
        ?string $performedBy = null
    ): self {
        return static::log([
            'jury_member_id' => $juryMemberId,
            'job_posting_id' => $jobPostingId,
            'event_type' => 'CONFLICT_REPORTED',
            'description' => "Conflicto de tipo {$conflictType} reportado",
            'metadata' => [
                'conflict_type' => $conflictType,
                'application_id' => $applicationId,
            ],
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }

    /**
     * Attributes
     */
    public function getPerformerNameAttribute(): string
    {
        return $this->performedBy->name ?? 'Sistema';
    }

    public function getEventTypeNameAttribute(): string
    {
        $types = [
            'ASSIGNED' => 'Asignado',
            'REPLACED' => 'Reemplazado',
            'EXCUSED' => 'Excusado',
            'REMOVED' => 'Removido',
            'SUSPENDED' => 'Suspendido',
            'REACTIVATED' => 'Reactivado',
            'TRAINING_COMPLETED' => 'Capacitación Completada',
            'CONFLICT_REPORTED' => 'Conflicto Reportado',
            'CONFLICT_RESOLVED' => 'Conflicto Resuelto',
            'WORKLOAD_UPDATED' => 'Carga Actualizada',
            'STATUS_CHANGED' => 'Estado Cambiado',
        ];

        return $types[$this->event_type] ?? $this->event_type;
    }

    public function getFormattedChangesAttribute(): ?string
    {
        if (!$this->old_values && !$this->new_values) {
            return null;
        }

        $changes = [];
        $allKeys = array_unique(array_merge(
            array_keys($this->old_values ?? []),
            array_keys($this->new_values ?? [])
        ));

        foreach ($allKeys as $key) {
            $old = $this->old_values[$key] ?? 'N/A';
            $new = $this->new_values[$key] ?? 'N/A';

            if ($old !== $new) {
                $changes[] = ucfirst($key) . ": {$old} → {$new}";
            }
        }

        return implode(', ', $changes);
    }
}