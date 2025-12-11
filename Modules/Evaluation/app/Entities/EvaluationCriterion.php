<?php

namespace Modules\Evaluation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\{HasUuid, HasMetadata};
use Modules\Evaluation\Enums\ScoreTypeEnum;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};

class EvaluationCriterion extends Model
{
    use HasUuid, HasMetadata, SoftDeletes, HasFactory;

    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'uuid',
        'phase_id',
        'job_posting_id',
        'code',
        'name',
        'description',
        'min_score',
        'max_score',
        'weight',
        'order',
        'requires_comment',
        'requires_evidence',
        'score_type',
        'score_scales',
        'evaluation_guide',
        'is_active',
        'is_system',
        'metadata',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'order' => 'integer',
        'requires_comment' => 'boolean',
        'requires_evidence' => 'boolean',
        'score_type' => ScoreTypeEnum::class,
        'score_scales' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relaciones
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProcessPhase::class, 'phase_id');
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(EvaluationDetail::class, 'criterion_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPhase($query, $phaseId)
    {
        return $query->where('phase_id', $phaseId);
    }

    public function scopeByJobPosting($query, $jobPostingId)
    {
        return $query->where(function ($q) use ($jobPostingId) {
            $q->where('job_posting_id', $jobPostingId)
              ->orWhereNull('job_posting_id');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function requiresComment(): bool
    {
        return $this->requires_comment;
    }

    public function requiresEvidence(): bool
    {
        return $this->requires_evidence;
    }

    public function getScoreRange(): array
    {
        return [
            'min' => $this->min_score,
            'max' => $this->max_score,
        ];
    }

    public function validateScore(float $score): bool
    {
        return $score >= $this->min_score && $score <= $this->max_score;
    }

    public function calculateWeightedScore(float $score): float
    {
        return $score * $this->weight;
    }
}