<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PositionCode extends BaseSoftDelete
{
    use HasUuid, HasMetadata;

    protected $fillable = [
        'code',
        'name', // Este es el cargo
        'description',
        'base_salary',
        'essalud_percentage',
        'contract_months',
        'is_active',
        
        // Nuevos campos desde el JSON
        'min_professional_experience',
        'min_specific_experience',
        'requires_professional_title',
        'requires_professional_license',
        'education_level_required',
        
        'metadata',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'essalud_percentage' => 'decimal:2',
        'essalud_amount' => 'decimal:2',
        'monthly_total' => 'decimal:2',
        'quarterly_total' => 'decimal:2',
        'contract_months' => 'integer',
        'is_active' => 'boolean',
        'min_professional_experience' => 'decimal:1',
        'min_specific_experience' => 'decimal:1',
        'requires_professional_title' => 'boolean',
        'requires_professional_license' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_base_salary',
        'formatted_monthly_total',
        'formatted_quarterly_total',
    ];

    protected $searchable = ['code', 'name', 'description'];
    protected $sortable = ['code', 'name', 'base_salary', 'created_at'];

    // Relaciones
    public function jobProfiles(): HasMany
    {
        return $this->hasMany(JobProfile::class);
    }

    public function evaluationCriteria(): HasMany
    {
        return $this->hasMany(EvaluationCriterion::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // Accessors
    public function getFormattedBaseSalaryAttribute(): string
    {
        return 'S/ ' . number_format($this->base_salary, 2);
    }

    public function getFormattedMonthlyTotalAttribute(): string
    {
        return 'S/ ' . number_format($this->monthly_total, 2);
    }

    public function getFormattedQuarterlyTotalAttribute(): string
    {
        return 'S/ ' . number_format($this->quarterly_total, 2);
    }

    // Métodos de negocio
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function calculateEssalud(): float
    {
        return round($this->base_salary * ($this->essalud_percentage / 100), 2);
    }

    public function calculateMonthlyTotal(): float
    {
        return round($this->base_salary + $this->calculateEssalud(), 2);
    }

    public function calculateQuarterlyTotal(): float
    {
        return round($this->calculateMonthlyTotal() * $this->contract_months, 2);
    }

    /**
     * Obtiene los datos para autocompletar el JobProfile
     */
    public function getJobProfileDefaults(): array
    {
        return [
            // profile_name se mantiene libre, no se autocompleta
            'salary_min' => $this->base_salary,
            'salary_max' => $this->base_salary, // Solo el salario base
            'education_level' => $this->education_level_required,
            'title_required' => $this->requires_professional_title,
            'colegiatura_required' => $this->requires_professional_license,
            'general_experience_years' => $this->min_professional_experience,
            'specific_experience_years' => $this->min_specific_experience,
        ];
    }
}