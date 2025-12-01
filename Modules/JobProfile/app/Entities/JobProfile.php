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
        'job_posting_id',
        'title',
        'profile_name',
        'organizational_unit_id',
        'position_code_id',
        'requesting_unit_id',
        'job_level',
        'contract_type',
        'salary_min',
        'salary_max',
        'description',
        'mission',
        'working_conditions',

        // Requisitos académicos
        'education_level',
        'career_field',
        'title_required',
        'colegiatura_required',

        // Experiencia
        'general_experience_years',
        'specific_experience_years',
        'specific_experience_description',

        // Capacitación, conocimientos, competencias
        'required_courses',
        'knowledge_areas',
        'required_competencies',

        // Funciones del puesto
        'main_functions',

        // Régimen laboral
        'work_regime',
        'justification',

        // Vacantes
        'total_vacancies',

        // Estado y revisión
        'status',
        'requested_by',
        'reviewed_by',
        'approved_by',
        'requested_at',
        'reviewed_at',
        'approved_at',
        'review_comments',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'colegiatura_required' => 'boolean',
        'general_experience_years' => 'decimal:1',
        'specific_experience_years' => 'decimal:1',
        'required_courses' => 'array',
        'knowledge_areas' => 'array',
        'required_competencies' => 'array',
        'main_functions' => 'array',
        'total_vacancies' => 'integer',
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

    // Relaciones
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobPosting\Entities\JobPosting::class);
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Organization\Entities\OrganizationalUnit::class);
    }

    public function positionCode(): BelongsTo
    {
        return $this->belongsTo(PositionCode::class);
    }

    public function requestingUnit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Organization\Entities\OrganizationalUnit::class, 'requesting_unit_id');
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

    public function vacancies(): HasMany
    {
        return $this->hasMany(JobProfileVacancy::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(JobProfileHistory::class)->orderBy('created_at', 'desc');
    }

    // Scopes
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

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    public function scopeByJobPosting($query, string $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeByPositionCode($query, string $positionCodeId)
    {
        return $query->where('position_code_id', $positionCodeId);
    }

    public function scopeByEducationLevel($query, string $educationLevel)
    {
        return $query->where('education_level', $educationLevel);
    }

    public function scopeByWorkRegime($query, string $workRegime)
    {
        return $query->where('work_regime', $workRegime);
    }

    /**
     * Scope para filtrar solo los perfiles solicitados por el usuario
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('requested_by', $userId);
    }

    /**
     * Scope para filtrar perfiles según permisos del usuario
     */
    public function scopeVisibleFor($query, $user)
    {
        // Si tiene permiso para ver todos, no filtrar
        if ($user->hasPermission('jobprofile.view.profiles')) {
            return $query;
        }

        // Si solo puede ver los propios, filtrar por requested_by
        if ($user->hasPermission('jobprofile.view.own')) {
            return $query->where('requested_by', $user->id);
        }

        // Si no tiene ningún permiso, no mostrar nada
        return $query->whereRaw('1 = 0');
    }

    // Métodos de estado
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInReview(): bool
    {
        return $this->status === 'in_review';
    }

    public function isModificationRequested(): bool
    {
        return $this->status === 'modification_requested';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'modification_requested']);
    }

    public function canSubmitForReview(): bool
    {
        return in_array($this->status, ['draft', 'modification_requested']);
    }

    public function canApprove(): bool
    {
        return $this->status === 'in_review';
    }

    public function canReject(): bool
    {
        return $this->status === 'in_review';
    }

    public function canRequestModification(): bool
    {
        return $this->status === 'in_review';
    }

    // Accessors
    public function getSalaryRangeAttribute(): string
    {
        if ($this->salary_min && $this->salary_max) {
            return 'S/ ' . number_format($this->salary_min, 2) . ' - S/ ' . number_format($this->salary_max, 2);
        }
        return 'No especificado';
    }

    public function getEducationLevelLabelAttribute(): string
    {
        if (!$this->education_level) {
            return 'No especificado';
        }
        return \Modules\JobProfile\Enums\EducationLevelEnum::from($this->education_level)->label();
    }

    public function getWorkRegimeLabelAttribute(): string
    {
        if (!$this->work_regime) {
            return 'No especificado';
        }
        return \Modules\JobProfile\Enums\WorkRegimeEnum::from($this->work_regime)->label();
    }

    public function getStatusLabelAttribute(): string
    {
        return \Modules\JobProfile\Enums\JobProfileStatusEnum::from($this->status)->label();
    }

    public function getStatusBadgeAttribute(): string
    {
        return \Modules\JobProfile\Enums\JobProfileStatusEnum::from($this->status)->badge();
    }

    public function getTotalExperienceYearsAttribute(): float
    {
        return ($this->general_experience_years ?? 0) + ($this->specific_experience_years ?? 0);
    }
}
