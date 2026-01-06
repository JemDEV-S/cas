<?php

namespace Modules\Application\Entities;

use Modules\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entity TempJobProfileCareerMapping
 *
 * Almacena mapeos temporales de job_profiles a carreras
 * que requieren revisión manual antes de ser aprobados.
 */
class TempJobProfileCareerMapping extends BaseModel
{
    use HasUuids;

    protected $fillable = [
        'job_profile_id',
        'career_id',
        'original_text',
        'confidence_score',
        'status',
        'reviewed_by',
        'reviewed_at',
        'notes',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $searchable = ['original_text', 'notes'];
    protected $sortable = ['confidence_score', 'status', 'created_at'];

    /**
     * Relación con job profile
     */
    public function jobProfile(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobProfile\Entities\JobProfile::class, 'job_profile_id');
    }

    /**
     * Relación con carrera
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(AcademicCareer::class, 'career_id');
    }

    /**
     * Relación con el revisor
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(\Modules\User\Entities\User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'PENDING_REVIEW');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    public function scopeLowConfidence($query, float $threshold = 90.0)
    {
        return $query->where('confidence_score', '<', $threshold);
    }

    /**
     * Aprobar mapeo y mover a job_profile_careers
     */
    public function approve(string $userId): bool
    {
        if (!$this->career_id) {
            return false;
        }

        $this->status = 'APPROVED';
        $this->reviewed_by = $userId;
        $this->reviewed_at = now();
        $this->save();

        // Crear registro en job_profile_careers
        \Modules\JobProfile\Entities\JobProfileCareer::create([
            'job_profile_id' => $this->job_profile_id,
            'career_id' => $this->career_id,
            'is_primary' => false,
            'mapping_source' => 'MANUAL',
            'mapped_from_text' => $this->original_text,
            'confidence_score' => $this->confidence_score,
        ]);

        return true;
    }

    /**
     * Rechazar mapeo
     */
    public function reject(string $userId, ?string $reason = null): bool
    {
        $this->status = 'REJECTED';
        $this->reviewed_by = $userId;
        $this->reviewed_at = now();
        if ($reason) {
            $this->notes = $reason;
        }
        return $this->save();
    }
}
