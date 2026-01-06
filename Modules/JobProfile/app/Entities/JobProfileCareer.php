<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entity JobProfileCareer
 *
 * Tabla pivote entre JobProfile y AcademicCareer.
 * Reemplaza el parsing de career_field para validaciones rápidas y precisas.
 */
class JobProfileCareer extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'job_profile_id',
        'career_id',
        'is_primary',
        'mapping_source',
        'mapped_from_text',
        'confidence_score',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'confidence_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $searchable = ['mapped_from_text'];
    protected $sortable = ['is_primary', 'confidence_score', 'created_at'];

    /**
     * Relación con job profile
     */
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(JobProfile::class, 'job_profile_id');
    }

    /**
     * Relación con carrera académica
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(\Modules\Application\Entities\AcademicCareer::class, 'career_id');
    }

    /**
     * Scopes
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByJobProfile($query, string $jobProfileId)
    {
        return $query->where('job_profile_id', $jobProfileId);
    }

    public function scopeByCareer($query, string $careerId)
    {
        return $query->where('career_id', $careerId);
    }

    public function scopeAutoMapped($query)
    {
        return $query->where('mapping_source', 'AUTO');
    }

    public function scopeManualMapped($query)
    {
        return $query->where('mapping_source', 'MANUAL');
    }

    public function scopeMigrated($query)
    {
        return $query->where('mapping_source', 'MIGRATION');
    }

    public function scopeHighConfidence($query, float $threshold = 90.0)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    /**
     * Marcar como carrera primaria
     */
    public function markAsPrimary(): bool
    {
        // Desmarcar otras carreras primarias del mismo perfil
        self::where('job_profile_id', $this->job_profile_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->is_primary = true;
        return $this->save();
    }
}
