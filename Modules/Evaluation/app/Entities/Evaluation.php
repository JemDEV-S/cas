<?php

namespace Modules\Evaluation\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Evaluation\Enums\EvaluationStatusEnum;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};
use Illuminate\Support\Str;

class Evaluation extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'evaluations';

    protected $fillable = [
        'uuid',
        'application_id',
        'evaluator_id',
        'phase_id',
        'job_posting_id',
        'status',
        'total_score',
        'max_possible_score',
        'percentage',
        'submitted_at',
        'deadline_at',
        'is_anonymous',
        'is_collaborative',
        'general_comments',
        'internal_notes',
        'modified_by',
        'modified_at',
        'modification_reason',
        'metadata',
    ];

    protected $casts = [
        'status' => EvaluationStatusEnum::class,
        'total_score' => 'decimal:2',
        'max_possible_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'submitted_at' => 'datetime',
        'deadline_at' => 'datetime',
        'modified_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'is_collaborative' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relaciones
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo('Modules\Application\Entities\Application');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'evaluator_id');
    }

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
        return $this->hasMany(EvaluationDetail::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(EvaluationHistory::class);
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'modified_by');
    }

    /**
     * Scopes
     */
    public function scopeByEvaluator($query, $evaluatorId)
    {
        return $query->where('evaluator_id', $evaluatorId);
    }

    public function scopeByPhase($query, $phaseId)
    {
        return $query->where('phase_id', $phaseId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            EvaluationStatusEnum::ASSIGNED->value,
            EvaluationStatusEnum::IN_PROGRESS->value
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [
            EvaluationStatusEnum::SUBMITTED->value,
            EvaluationStatusEnum::MODIFIED->value
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline_at', '<', now())
            ->whereIn('status', [
                EvaluationStatusEnum::ASSIGNED->value,
                EvaluationStatusEnum::IN_PROGRESS->value
            ]);
    }

    /**
     * Helper Methods
     */
    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    public function isOverdue(): bool
    {
        return $this->deadline_at && $this->deadline_at->isPast() && !$this->isCompleted();
    }

    public function calculateTotalScore(): float
    {
        return $this->details()->sum('weighted_score') ?? 0;
    }

    public function calculatePercentage(): float
    {
        if ($this->max_possible_score > 0) {
            return ($this->total_score / $this->max_possible_score) * 100;
        }
        return 0;
    }

    /**
     * Actualizar puntajes
     */
    public function updateScores(): void
    {
        $this->total_score = $this->calculateTotalScore();
        $this->percentage = $this->calculatePercentage();
        $this->save();
    }

    /**
     * Enviar evaluación
     */
    public function submit(): void
    {
        $this->status = EvaluationStatusEnum::SUBMITTED;
        $this->submitted_at = now();
        $this->save();
    }
}