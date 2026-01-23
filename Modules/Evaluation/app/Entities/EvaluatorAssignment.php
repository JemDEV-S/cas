<?php

namespace Modules\Evaluation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Evaluation\Enums\AssignmentStatusEnum;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};
use Illuminate\Support\Str;

class EvaluatorAssignment extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'evaluator_assignments';

    protected $fillable = [
        'uuid',
        'user_id',
        'application_id',
        'phase_id',
        'job_posting_id',
        'assignment_type',
        'assigned_by',
        'assigned_at',
        'status',
        'deadline_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'assignment_type' => 'string',
        'status' => AssignmentStatusEnum::class,
        'assigned_at' => 'datetime',
        'deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('Modules\User\Entities\User', 'user_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->user();
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo('Modules\Application\Entities\Application');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProcessPhase::class, 'phase_id');
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'assigned_by');
    }

    public function juryAssignment(): BelongsTo
    {
        return $this->belongsTo(\Modules\Jury\Entities\JuryAssignment::class, 'user_id', 'user_id')
            ->where('job_posting_id', $this->job_posting_id);
    }

    public function isEvaluatorAvailable(): bool
    {
        if (!$this->user) {
            return false;
        }

        $juryAssignment = \Modules\Jury\Entities\JuryAssignment::where('user_id', $this->user_id)
            ->where('job_posting_id', $this->job_posting_id)
            ->where('status', 'ACTIVE')
            ->first();

        return $juryAssignment && $juryAssignment->canEvaluate();
    }

    /**
     * Scopes
     */
    public function scopeByEvaluator($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByApplication($query, $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    public function scopeByPhase($query, $phaseId)
    {
        return $query->where('phase_id', $phaseId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            AssignmentStatusEnum::PENDING->value,
            AssignmentStatusEnum::IN_PROGRESS->value,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', AssignmentStatusEnum::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', AssignmentStatusEnum::COMPLETED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline_at', '<', now())
            ->active();
    }

    public function scopeAvailable($query)
    {
        return $query->whereDoesntHave('conflicts');
    }

    public function conflicts()
    {
        return $this->hasMany(\Modules\Jury\Entities\JuryConflict::class, 'user_id', 'user_id')
            ->where('application_id', $this->application_id);
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isOverdue(): bool
    {
        return $this->deadline_at && $this->deadline_at->isPast() && $this->isActive();
    }

    public function markAsInProgress(): void
    {
        $this->status = AssignmentStatusEnum::IN_PROGRESS;
        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->status = AssignmentStatusEnum::COMPLETED;
        $this->completed_at = now();
        $this->save();
    }

    public function cancel(string $reason = null): void
    {
        $this->status = AssignmentStatusEnum::CANCELLED;
        if ($reason) {
            $this->metadata = array_merge($this->metadata ?? [], ['cancellation_reason' => $reason]);
        }
        $this->save();
    }

    public function reassign(int $newUserId, int $reassignedBy): self
    {
        $this->status = AssignmentStatusEnum::REASSIGNED;
        $this->save();

        // Crear nueva asignación
        return static::create([
            'user_id' => $newUserId,
            'application_id' => $this->application_id,
            'phase_id' => $this->phase_id,
            'job_posting_id' => $this->job_posting_id,
            'assignment_type' => 'MANUAL',
            'assigned_by' => $reassignedBy,
            'deadline_at' => $this->deadline_at,
        ]);
    }
}
