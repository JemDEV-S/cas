<?php

namespace Modules\Evaluation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EvaluationDetail extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'evaluation_details';

    protected $fillable = [
        'uuid',
        'evaluation_id',
        'criterion_id',
        'score',
        'weighted_score',
        'comments',
        'evidence',
        'version',
        'change_reason',
        'metadata',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'version' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Generar UUID al crear
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        // Calcular weighted_score automáticamente al guardar
        static::saving(function ($detail) {
            if ($detail->criterion) {
                $detail->weighted_score = $detail->calculateWeightedScore();
            }
        });

        // Actualizar el total de la evaluación después de guardar
        static::saved(function ($detail) {
            if ($detail->evaluation) {
                $detail->evaluation->updateScores();
            }
        });

        // Actualizar el total de la evaluación después de eliminar
        static::deleted(function ($detail) {
            if ($detail->evaluation) {
                $detail->evaluation->updateScores();
            }
        });
    }

    /**
     * Relaciones
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterion::class, 'criterion_id');
    }

    /**
     * Scopes
     */
    public function scopeByEvaluation($query, $evaluationId)
    {
        return $query->where('evaluation_id', $evaluationId);
    }

    public function scopeByCriterion($query, $criterionId)
    {
        return $query->where('criterion_id', $criterionId);
    }

    /**
     * Helper Methods
     */
    public function calculateWeightedScore(): float
    {
        if ($this->criterion) {
            return $this->criterion->calculateWeightedScore($this->score);
        }
        return $this->score;
    }

    public function validateScore(): bool
    {
        if ($this->criterion) {
            return $this->criterion->validateScore($this->score);
        }
        return true;
    }

    /**
     * Actualizar puntaje ponderado
     */
    public function updateWeightedScore(): void
    {
        $this->weighted_score = $this->calculateWeightedScore();
        $this->save();
    }

}