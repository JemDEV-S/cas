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
        'evaluator_id',
        'application_id',
        'phase_id',
        'job_posting_id',
        'assignment_type',
        'assigned_by',
        'assigned_at',
        'status',
        'workload_weight',
        'deadline_at',
        'completed_at',
        'has_conflict',
        'conflict_reason',
        'is_available',
        'unavailability_reason',
        'notified',
        'notified_at',
        'metadata',
    ];

    protected $casts = [
        'assignment_type' => 'string',
        'status' => AssignmentStatusEnum::class,
        'assigned_at' => 'datetime',
        'deadline_at' => 'datetime',
        'completed_at' => 'datetime',
        'workload_weight' => 'integer',
        'has_conflict' => 'boolean',
        'is_available' => 'boolean',
        'notified' => 'boolean',
        'notified_at' => 'datetime',
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
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'evaluator_id');
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

    /**
     * Scopes
     */
    public function scopeByEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
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
        return $query->where('is_available', true)
            ->where('has_conflict', false);
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

    public function reassign(int $newEvaluatorId, int $reassignedBy): self
    {
        $this->status = AssignmentStatusEnum::REASSIGNED;
        $this->save();

        // Crear nueva asignación
        return static::create([
            'evaluator_id' => $newEvaluatorId,
            'application_id' => $this->application_id,
            'phase_id' => $this->phase_id,
            'job_posting_id' => $this->job_posting_id,
            'assignment_type' => 'MANUAL',
            'assigned_by' => $reassignedBy,
            'deadline_at' => $this->deadline_at,
            'workload_weight' => $this->workload_weight,
        ]);
    }

    public function markAsNotified(): void
    {
        $this->notified = true;
        $this->notified_at = now();
        $this->save();
    }
}