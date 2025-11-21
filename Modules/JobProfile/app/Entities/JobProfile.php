<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasStatus;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobProfile extends BaseSoftDelete
{
    use HasUuid, HasStatus, HasMetadata;

    protected $fillable = [
        'code',
        'title',
        'organizational_unit_id',
        'job_level',
        'contract_type',
        'salary_min',
        'salary_max',
        'description',
        'mission',
        'working_conditions',
        'status',
        'requested_by',
        'reviewed_by',
        'approved_by',
        'requested_at',
        'reviewed_at',
        'approved_at',
        'metadata',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $searchable = ['code', 'title', 'description'];
    protected $sortable = ['code', 'title', 'job_level', 'status', 'created_at'];

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Organization\Entities\OrganizationalUnit::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'approved_by');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(JobProfileRequirement::class);
    }

    public function responsibilities(): HasMany
    {
        return $this->hasMany(JobProfileResponsibility::class);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getSalaryRangeAttribute(): string
    {
        return 'S/ ' . number_format($this->salary_min, 2) . ' - S/ ' . number_format($this->salary_max, 2);
    }
}
