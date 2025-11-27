<?php

namespace Modules\JobProfile\Entities;

use Modules\Core\Entities\BaseSoftDelete;
use Modules\Core\Traits\HasUuid;
use Modules\Core\Traits\HasMetadata;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationCriterion extends BaseSoftDelete
{
    use HasUuid, HasMetadata;

    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'position_code_id',
        'process_phase_id',
        'name',
        'description',
        'min_score',
        'max_score',
        'weight',
        'order',
        'is_required',
        'metadata',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'order' => 'integer',
        'is_required' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'score_range',
    ];

    protected $searchable = ['name', 'description'];
    protected $sortable = ['name', 'order', 'weight', 'max_score'];

    // Relaciones
    public function positionCode(): BelongsTo
    {
        return $this->belongsTo(PositionCode::class);
    }

    public function processPhase(): BelongsTo
    {
        return $this->belongsTo(\Modules\JobPosting\Entities\ProcessPhase::class);
    }

    // Scopes
    public function scopeByPositionCode($query, string $positionCodeId)
    {
        return $query->where('position_code_id', $positionCodeId);
    }

    public function scopeByPhase($query, string $phaseId)
    {
        return $query->where('process_phase_id', $phaseId);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // Accessors
    public function getScoreRangeAttribute(): string
    {
        return number_format($this->min_score, 2) . ' - ' . number_format($this->max_score, 2) . ' puntos';
    }

    public function getWeightPercentageAttribute(): string
    {
        return number_format($this->weight, 2) . '%';
    }

    // Métodos de negocio
    public function isScoreValid(float $score): bool
    {
        return $score >= $this->min_score && $score <= $this->max_score;
    }

    public function calculateWeightedScore(float $score): float
    {
        if (!$this->isScoreValid($score)) {
            throw new \InvalidArgumentException(
                "El puntaje {$score} no está dentro del rango permitido ({$this->min_score} - {$this->max_score})"
            );
        }

        // Calcula el puntaje ponderado según el peso del criterio
        return round(($score / $this->max_score) * $this->weight, 2);
    }
}
