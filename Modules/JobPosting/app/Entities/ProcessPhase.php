<?php

namespace Modules\JobPosting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Importante para factories
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\{HasUuid, HasMetadata};

class ProcessPhase extends Model
{
    use HasUuid, HasMetadata, SoftDeletes, HasFactory;

    protected $table = 'process_phases'; // Buena práctica explicitar la tabla

    protected $fillable = [
        'code',
        'name',
        'description',
        'phase_number',
        'order', // Opcional, si quieres un orden visual distinto al número de fase
        'requires_evaluation',
        'is_public',
        'is_active',
        'is_system', // <--- AGREGADO: Para proteger las fases base
        'default_duration_days',
        'default_duration_hours',
        'metadata',
    ];

    protected $casts = [
        'phase_number' => 'integer',
        'order' => 'integer',
        'requires_evaluation' => 'boolean',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean', // <--- AGREGADO
        'default_duration_days' => 'integer',
        'default_duration_hours' => 'integer',
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
        // CAMBIO: Usamos 'phase_number' como orden principal para asegurar
        // que salgan en el orden del 1 al 12 como en el Seeder.
        return $query->orderBy('phase_number', 'asc')->orderBy('order', 'asc');
    }
    
    /**
     * Scope para obtener solo las fases del sistema (las 12 base)
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Helpers
     */
    public function requiresEvaluation(): bool
    {
        return $this->requires_evaluation;
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}