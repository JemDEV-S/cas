<?php

namespace Modules\JobPosting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\{HasUuid, HasMetadata};

class ProcessPhase extends Model
{
    use HasUuid, HasMetadata, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'phase_number',
        'order',
        'requires_evaluation',
        'is_public',
        'is_active',
        'default_duration_days',
        'metadata',
    ];

    protected $casts = [
        'phase_number' => 'integer',
        'order' => 'integer',
        'requires_evaluation' => 'boolean',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'default_duration_days' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Cronogramas que usan esta fase
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(JobPostingSchedule::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequiresEvaluation($query)
    {
        return $query->where('requires_evaluation', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Verificar si requiere evaluación
     */
    public function requiresEvaluation(): bool
    {
        return $this->requires_evaluation;
    }

    /**
     * Verificar si es pública
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * Verificar si está activa
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}