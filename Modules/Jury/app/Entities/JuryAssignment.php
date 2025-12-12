<?php

namespace Modules\Jury\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Jury\Enums\{MemberType, JuryRole, AssignmentStatus};
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Str;

class JuryAssignment extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'jury_assignments';

    protected $fillable = [
        'jury_member_id',
        'job_posting_id',
        'member_type',
        'role_in_jury',
        'order',
        'assigned_by',
        'assigned_at',
        'assignment_resolution',
        'resolution_date',
        'status',
        'is_active',
        'replaced_by',
        'replacement_reason',
        'replacement_date',
        'replacement_approved_by',
        'excuse_reason',
        'excused_at',
        'excused_by',
        'has_declared_conflicts',
        'conflict_declaration',
        'conflict_declared_at',
        'max_evaluations',
        'current_evaluations',
        'completed_evaluations',
        'available_from',
        'available_until',
        'notified',
        'notified_at',
        'accepted',
        'accepted_at',
        'metadata',
    ];

    protected $casts = [
        'member_type' => MemberType::class,
        'role_in_jury' => JuryRole::class,
        'status' => AssignmentStatus::class,
        'order' => 'integer',
        'assigned_at' => 'datetime',
        'resolution_date' => 'date',
        'is_active' => 'boolean',
        'replacement_date' => 'datetime',
        'excused_at' => 'datetime',
        'has_declared_conflicts' => 'boolean',
        'conflict_declared_at' => 'datetime',
        'max_evaluations' => 'integer',
        'current_evaluations' => 'integer',
        'completed_evaluations' => 'integer',
        'available_from' => 'date',
        'available_until' => 'date',
        'notified' => 'boolean',
        'notified_at' => 'datetime',
        'accepted' => 'boolean',
        'accepted_at' => 'datetime',
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

            if (empty($model->assigned_at)) {
                $model->assigned_at = now();
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

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'assigned_by');
    }

    public function replacedBy(): BelongsTo
    {
        return $this->belongsTo(JuryMember::class, 'replaced_by');
    }

    public function replacementApprovedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'replacement_approved_by');
    }

    public function excusedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'excused_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', AssignmentStatus::ACTIVE)
            ->where('is_active', true);
    }

    public function scopeByJobPosting($query, string $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeByJuryMember($query, string $juryMemberId)
    {
        return $query->where('jury_member_id', $juryMemberId);
    }

    public function scopeTitular($query)
    {
        return $query->where('member_type', MemberType::TITULAR);
    }

    public function scopeSuplente($query)
    {
        return $query->where('member_type', MemberType::SUPLENTE);
    }

    public function scopeByRole($query, JuryRole $role)
    {
        return $query->where('role_in_jury', $role);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    public function scopeWithWorkload($query)
    {
        return $query->addSelect([
            'workload_percentage' => function ($q) {
                $q->selectRaw('CASE WHEN max_evaluations > 0 THEN (current_evaluations * 100 / max_evaluations) ELSE 0 END');
            }
        ]);
    }

    /**
     * Helper Methods
     */
    public function canEvaluate(): bool
    {
        return $this->status === AssignmentStatus::ACTIVE 
            && $this->is_active 
            && !$this->isOverloaded();
    }

    public function isOverloaded(): bool
    {
        if (!$this->max_evaluations) {
            return false;
        }

        return $this->current_evaluations >= $this->max_evaluations;
    }

    public function hasCapacity(): bool
    {
        return !$this->isOverloaded();
    }

    public function getAvailableSlots(): int
    {
        if (!$this->max_evaluations) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_evaluations - $this->current_evaluations);
    }

    public function incrementWorkload(int $amount = 1): void
    {
        $this->increment('current_evaluations', $amount);
    }

    public function decrementWorkload(int $amount = 1): void
    {
        $this->decrement('current_evaluations', max(0, $amount));
    }

    public function completeEvaluation(): void
    {
        $this->increment('completed_evaluations');
        $this->decrement('current_evaluations');
    }

    public function replace(string $newJuryMemberId, string $reason, ?string $approvedBy = null): void
    {
        $this->update([
            'status' => AssignmentStatus::REPLACED,
            'is_active' => false,
            'replaced_by' => $newJuryMemberId,
            'replacement_reason' => $reason,
            'replacement_date' => now(),
            'replacement_approved_by' => $approvedBy,
        ]);
    }

    public function excuse(string $reason, ?string $excusedBy = null): void
    {
        $this->update([
            'status' => AssignmentStatus::EXCUSED,
            'is_active' => false,
            'excuse_reason' => $reason,
            'excused_at' => now(),
            'excused_by' => $excusedBy,
        ]);
    }

    public function remove(): void
    {
        $this->update([
            'status' => AssignmentStatus::REMOVED,
            'is_active' => false,
        ]);
    }

    public function suspend(): void
    {
        $this->update([
            'status' => AssignmentStatus::SUSPENDED,
            'is_active' => false,
        ]);
    }

    public function reactivate(): void
    {
        $this->update([
            'status' => AssignmentStatus::ACTIVE,
            'is_active' => true,
        ]);
    }

    public function markAsNotified(): void
    {
        $this->update([
            'notified' => true,
            'notified_at' => now(),
        ]);
    }

    public function accept(): void
    {
        $this->update([
            'accepted' => true,
            'accepted_at' => now(),
        ]);
    }

    public function declareConflicts(string $declaration): void
    {
        $this->update([
            'has_declared_conflicts' => true,
            'conflict_declaration' => $declaration,
            'conflict_declared_at' => now(),
        ]);
    }

    /**
     * Attributes
     */
    public function getJuryMemberNameAttribute(): string
    {
        return $this->juryMember->full_name ?? 'N/A';
    }

    public function getJobPostingTitleAttribute(): string
    {
        return $this->jobPosting->title ?? 'N/A';
    }

    public function getWorkloadPercentageAttribute(): int
    {
        if (!$this->max_evaluations || $this->max_evaluations == 0) {
            return 0;
        }

        return (int) (($this->current_evaluations / $this->max_evaluations) * 100);
    }

    public function getCompletionPercentageAttribute(): int
    {
        if ($this->current_evaluations == 0) {
            return 0;
        }

        $total = $this->current_evaluations + $this->completed_evaluations;
        return (int) (($this->completed_evaluations / $total) * 100);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->jury_member_name;
        $type = $this->member_type?->label() ?? '';
        $role = $this->role_in_jury?->label() ?? '';

        return trim("{$name} - {$type}" . ($role ? " ({$role})" : ''));
    }
}