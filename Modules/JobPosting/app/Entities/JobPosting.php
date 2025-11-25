<?php

namespace Modules\JobPosting\Entities;

use Modules\Core\Entities\BaseModel;
use Modules\Core\Traits\{HasUuid, HasStatus, HasMetadata};
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends BaseModel
{
    use HasUuid, HasStatus, HasMetadata, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'year',
        'description',
        'status',
        'start_date',
        'end_date',
        'published_at',
        'published_by',
        'finalized_at',
        'finalized_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'metadata',
    ];

    protected $casts = [
        'status' => JobPostingStatusEnum::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'published_at' => 'datetime',
        'finalized_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->code) {
                $model->code = self::generateCode();
            }
            if (!$model->year) {
                $model->year = now()->year;
            }
            if (!$model->status) {
                $model->status = JobPostingStatusEnum::BORRADOR;
            }
        });
    }

    /**
     * Generar código único
     */
    protected static function generateCode(): string
    {
        $year = now()->year;
        $lastNumber = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('CONV-%d-%03d', $year, $lastNumber);
    }

    /**
     * Usuario que publicó
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Usuario que finalizó
     */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * Usuario que canceló
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Cronogramas de fases
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(JobPostingSchedule::class);
    }

    /**
     * Estados de fases
     */
    public function phaseStatuses(): HasMany
    {
        return $this->hasMany(JobPostingPhaseStatus::class);
    }

    /**
     * Historial de cambios
     */
    public function history(): HasMany
    {
        return $this->hasMany(JobPostingHistory::class);
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', JobPostingStatusEnum::PUBLICADA);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            JobPostingStatusEnum::PUBLICADA,
            JobPostingStatusEnum::EN_PROCESO,
        ]);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Verificar si está en borrador
     */
    public function isDraft(): bool
    {
        return $this->status === JobPostingStatusEnum::BORRADOR;
    }

    /**
     * Verificar si está publicada
     */
    public function isPublished(): bool
    {
        return $this->status === JobPostingStatusEnum::PUBLICADA;
    }

    /**
     * Verificar si está en proceso
     */
    public function isInProcess(): bool
    {
        return $this->status === JobPostingStatusEnum::EN_PROCESO;
    }

    /**
     * Verificar si está finalizada
     */
    public function isFinalized(): bool
    {
        return $this->status === JobPostingStatusEnum::FINALIZADA;
    }

    /**
     * Verificar si está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === JobPostingStatusEnum::CANCELADA;
    }

    /**
     * Puede ser editada
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Puede ser publicada
     */
    public function canBePublished(): bool
    {
        return $this->isDraft() && $this->hasCompleteSchedule();
    }

    /**
     * Puede ser cancelada
     */
    public function canBeCancelled(): bool
    {
        return !$this->isCancelled() && !$this->isFinalized();
    }

    /**
     * Tiene cronograma completo
     */
    public function hasCompleteSchedule(): bool
    {
        // Verificar que todas las fases tengan fechas programadas
        $requiredPhasesCount = ProcessPhase::where('is_active', true)->count();
        $scheduledPhasesCount = $this->schedules()->count();

        return $scheduledPhasesCount >= $requiredPhasesCount;
    }

    /**
     * Obtener fase actual
     */
    public function getCurrentPhase(): ?JobPostingSchedule
    {
        return $this->schedules()
            ->where('status', 'IN_PROGRESS')
            ->first();
    }

    /**
     * Progreso del proceso (porcentaje)
     */
    public function getProgressPercentage(): float
    {
        $totalPhases = $this->schedules()->count();

        if ($totalPhases === 0) {
            return 0;
        }

        $completedPhases = $this->schedules()
            ->where('status', 'COMPLETED')
            ->count();

        return ($completedPhases / $totalPhases) * 100;
    }
}
