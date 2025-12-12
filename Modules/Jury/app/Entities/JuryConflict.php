<?php

namespace Modules\Jury\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Jury\Enums\{ConflictType, ConflictSeverity, ConflictStatus};
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Str;

class JuryConflict extends Model
{
    use HasFactory;

    protected $table = 'jury_conflicts';

    protected $fillable = [
        'jury_member_id',
        'application_id',
        'job_posting_id',
        'applicant_id',
        'conflict_type',
        'severity',
        'description',
        'evidence_path',
        'additional_details',
        'status',
        'resolution',
        'resolved_by',
        'resolved_at',
        'action_taken',
        'action_notes',
        'reported_by',
        'reported_at',
        'is_self_reported',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'metadata',
    ];

    protected $casts = [
        'conflict_type' => ConflictType::class,
        'severity' => ConflictSeverity::class,
        'status' => ConflictStatus::class,
        'resolved_at' => 'datetime',
        'reported_at' => 'datetime',
        'is_self_reported' => 'boolean',
        'reviewed_at' => 'datetime',
        'additional_details' => 'array',
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

            if (empty($model->reported_at)) {
                $model->reported_at = now();
            }

            // Auto-detectar si es auto-reportado
            if (empty($model->is_self_reported) && $model->reported_by) {
                $juryMember = JuryMember::where('id', $model->jury_member_id)->first();
                $model->is_self_reported = $juryMember && $juryMember->user_id === $model->reported_by;
            }
        });
    }

    /**
     * Relaciones
     */
    public function juryMember(): BelongsTo
    {
        return $this->belongsTo(JuryMember::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo('Modules\Application\Entities\Application');
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'applicant_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'reported_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'resolved_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopeByJuryMember($query, string $juryMemberId)
    {
        return $query->where('jury_member_id', $juryMemberId);
    }

    public function scopeByApplication($query, string $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    public function scopeByJobPosting($query, string $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeByType($query, ConflictType $type)
    {
        return $query->where('conflict_type', $type);
    }

    public function scopeBySeverity($query, ConflictSeverity $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByStatus($query, ConflictStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ConflictStatus::pendingStatuses());
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ConflictStatus::closedStatuses());
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('severity', [ConflictSeverity::HIGH, ConflictSeverity::CRITICAL]);
    }

    public function scopeSelfReported($query)
    {
        return $query->where('is_self_reported', true);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('reported_at', '>=', now()->subDays($days));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('severity', 'desc')
            ->orderBy('reported_at', 'desc');
    }

    /**
     * Helper Methods
     */
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function isClosed(): bool
    {
        return $this->status->isClosed();
    }

    public function requiresImmediateAction(): bool
    {
        return $this->severity->requiresImmediateAction() && $this->isPending();
    }

    public function canTransitionTo(ConflictStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function moveToReview(?string $reviewedBy = null, ?string $notes = null): void
    {
        if (!$this->canTransitionTo(ConflictStatus::UNDER_REVIEW)) {
            throw new \Exception('Cannot transition to UNDER_REVIEW from current status');
        }

        $this->update([
            'status' => ConflictStatus::UNDER_REVIEW,
            'reviewed_by' => $reviewedBy ?? auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function confirm(?string $reviewedBy = null, ?string $notes = null): void
    {
        if (!$this->canTransitionTo(ConflictStatus::CONFIRMED)) {
            throw new \Exception('Cannot transition to CONFIRMED from current status');
        }

        $this->update([
            'status' => ConflictStatus::CONFIRMED,
            'reviewed_by' => $reviewedBy ?? auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function dismiss(?string $resolution = null, ?string $resolvedBy = null): void
    {
        if (!$this->canTransitionTo(ConflictStatus::DISMISSED)) {
            throw new \Exception('Cannot transition to DISMISSED from current status');
        }

        $this->update([
            'status' => ConflictStatus::DISMISSED,
            'resolution' => $resolution ?? 'Conflicto desestimado',
            'resolved_by' => $resolvedBy ?? auth()->id(),
            'resolved_at' => now(),
            'action_taken' => 'NO_ACTION',
        ]);
    }

    public function resolve(
        string $resolution,
        string $actionTaken,
        ?string $actionNotes = null,
        ?string $resolvedBy = null
    ): void {
        if (!$this->canTransitionTo(ConflictStatus::RESOLVED)) {
            throw new \Exception('Cannot transition to RESOLVED from current status');
        }

        $this->update([
            'status' => ConflictStatus::RESOLVED,
            'resolution' => $resolution,
            'action_taken' => $actionTaken,
            'action_notes' => $actionNotes,
            'resolved_by' => $resolvedBy ?? auth()->id(),
            'resolved_at' => now(),
        ]);
    }

    public function excuseJuryMember(?string $notes = null): void
    {
        $this->resolve(
            'Jurado excusado de la evaluación',
            'EXCUSED',
            $notes
        );
    }

    public function reassignEvaluation(?string $notes = null): void
    {
        $this->resolve(
            'Evaluación reasignada a otro jurado',
            'REASSIGNED',
            $notes
        );
    }

    /**
     * Attributes
     */
    public function getJuryMemberNameAttribute(): string
    {
        return $this->juryMember->full_name ?? 'N/A';
    }

    public function getReporterNameAttribute(): string
    {
        return $this->reportedBy->name ?? 'Sistema';
    }

    public function getApplicantNameAttribute(): string
    {
        if ($this->application) {
            return $this->application->full_name ?? 'N/A';
        }

        return $this->applicant->name ?? 'N/A';
    }

    public function getSeverityBadgeAttribute(): array
    {
        return [
            'label' => $this->severity->label(),
            'color' => $this->severity->color(),
        ];
    }

    public function getStatusBadgeAttribute(): array
    {
        return [
            'label' => $this->status->label(),
            'color' => $this->status->color(),
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->conflict_type->label();
    }

    public function getTypeIconAttribute(): string
    {
        return $this->conflict_type->icon();
    }

    public function getDaysOpenAttribute(): int
    {
        return $this->reported_at->diffInDays(
            $this->resolved_at ?? now()
        );
    }
}