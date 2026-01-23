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
        'user_id',
        'job_posting_id',
        'role_in_jury',
        'dependency_scope_id',
        'status',
        'assigned_by',
        'assigned_at',
        'metadata',
    ];

    protected $casts = [
        'role_in_jury' => JuryRole::class,
        'status' => AssignmentStatus::class,
        'assigned_at' => 'datetime',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'assigned_by');
    }

    public function dependencyScope(): BelongsTo
    {
        return $this->belongsTo('Modules\Organization\Entities\OrganizationalUnit', 'dependency_scope_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', AssignmentStatus::ACTIVE);
    }

    public function scopeByJobPosting($query, string $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByRole($query, JuryRole $role)
    {
        return $query->where('role_in_jury', $role);
    }

    public function scopeByDependency($query, string $dependencyId)
    {
        return $query->where('dependency_scope_id', $dependencyId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at');
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return $this->status === AssignmentStatus::ACTIVE;
    }

    public function canEvaluate(): bool
    {
        return $this->status === AssignmentStatus::ACTIVE;
    }

    public function deactivate(): void
    {
        $this->update([
            'status' => AssignmentStatus::INACTIVE,
        ]);
    }

    public function activate(): void
    {
        $this->update([
            'status' => AssignmentStatus::ACTIVE,
        ]);
    }

    /**
     * Attributes
     */
    public function getUserNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function getJobPostingTitleAttribute(): string
    {
        return $this->jobPosting->title ?? 'N/A';
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->user_name;
        $role = $this->role_in_jury?->label() ?? '';

        return trim("{$name}" . ($role ? " ({$role})" : ''));
    }
}
