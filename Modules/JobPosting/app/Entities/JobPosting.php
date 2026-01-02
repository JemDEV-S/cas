<?php

namespace Modules\JobPosting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\{HasUuid, HasMetadata};
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\JobPosting\Enums\ScheduleStatusEnum;
use Modules\User\Entities\User;

class JobPosting extends Model
{
    use HasUuid, HasMetadata, SoftDeletes;

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
        'results_published',
        'results_published_at',
        'results_published_by',
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
        'results_published' => 'boolean',
        'results_published_at' => 'datetime',
        'finalized_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
           /*if (!$model->code) {
                $model->code = self::generateCode();
            }*/
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
     * Usuario que publicó resultados
     */
    public function resultsPublisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'results_published_by');
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
     * Perfiles de puesto asociados a la convocatoria
     */
    public function jobProfiles(): HasMany
    {
        return $this->hasMany(\Modules\JobProfile\Entities\JobProfile::class);
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

    public function scopeDraft($query)
    {
        return $query->where('status', JobPostingStatusEnum::BORRADOR);
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
        $requiredPhasesCount = ProcessPhase::where('is_active', true)->count();
        $scheduledPhasesCount = $this->schedules()->count();

        return $scheduledPhasesCount >= $requiredPhasesCount;
    }

    /**
     * Obtener fase actual
     * MEJORADO: Con fallback por fechas/horas si no hay fase marcada como IN_PROGRESS
     */
    public function getCurrentPhase(): ?JobPostingSchedule
    {
        // 1. Buscar fase explícitamente marcada como IN_PROGRESS
        $inProgress = $this->schedules()
            ->where('status', ScheduleStatusEnum::IN_PROGRESS)
            ->with('phase')
            ->first();

        if ($inProgress) {
            return $inProgress;
        }

        // 2. Fallback: buscar por rango de fechas/horas
        $now = now();

        return $this->schedules()
            ->with('phase')
            ->get()
            ->first(function($schedule) use ($now) {
                // Combinar fecha y hora para comparación precisa
                $start = \Carbon\Carbon::parse($schedule->start_date);
                $end = \Carbon\Carbon::parse($schedule->end_date);

                // Agregar horas si existen
                if ($schedule->start_time) {
                    $timeParts = explode(':', $schedule->start_time);
                    $start->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0));
                }

                if ($schedule->end_time) {
                    $timeParts = explode(':', $schedule->end_time);
                    $end->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0));
                }

                // Verificar si NOW está dentro del rango
                return $now->gte($start) && $now->lte($end);
            });
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
            ->where('status', ScheduleStatusEnum::COMPLETED)
            ->count();

        return round(($completedPhases / $totalPhases) * 100, 2);
    }

    /**
     * Publicar convocatoria
     */
    public function publish(User $user): bool
    {
        if (!$this->canBePublished()) {
            return false;
        }

        return $this->update([
            'status' => JobPostingStatusEnum::PUBLICADA,
            'published_at' => now(),
            'published_by' => $user->id,
        ]);
    }

    /**
     * Iniciar proceso
     */
    public function startProcess(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        return $this->update([
            'status' => JobPostingStatusEnum::EN_PROCESO,
        ]);
    }

    /**
     * Finalizar convocatoria
     */
    public function finalize(User $user): bool
    {
        if (!$this->isInProcess()) {
            return false;
        }

        return $this->update([
            'status' => JobPostingStatusEnum::FINALIZADA,
            'finalized_at' => now(),
            'finalized_by' => $user->id,
        ]);
    }

    /**
     * Cancelar convocatoria
     */
    public function cancel(User $user, string $reason): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        return $this->update([
            'status' => JobPostingStatusEnum::CANCELADA,
            'cancelled_at' => now(),
            'cancelled_by' => $user->id,
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Obtener días restantes
     */
    public function getDaysRemaining(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Está próxima a finalizar
     */
    public function isNearingEnd(int $days = 7): bool
    {
        $remaining = $this->getDaysRemaining();
        return $remaining !== null && $remaining <= $days && $remaining > 0;
    }
}