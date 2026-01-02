<?php

namespace Modules\JobPosting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\{HasUuid, HasMetadata};
use Modules\JobPosting\Enums\ScheduleStatusEnum;
use Modules\Organization\Entities\OrganizationalUnit;

class JobPostingSchedule extends Model
{
    use HasUuid, HasMetadata, SoftDeletes;

    protected $table = 'job_posting_schedules';
    protected $fillable = [
        'job_posting_id',
        'process_phase_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'responsible_unit_id',
        'notes',
        'status',
        'actual_start_date',
        'actual_end_date',
        'notify_before',
        'notify_days_before',
        'notified_at',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_start_date' => 'datetime',
        'actual_end_date' => 'datetime',
        'notified_at' => 'datetime',
        'notify_before' => 'boolean',
        'notify_days_before' => 'integer',
        'status' => ScheduleStatusEnum::class,
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->status) {
                $model->status = ScheduleStatusEnum::PENDING;
            }
        });
    }
    public function getDaysRemaining(): int
    {
        $end = \Carbon\Carbon::parse($this->end_date);
        $now = now();

        return $end->isFuture() ? $now->diffInDays($end) : 0;
    }

    /**
     * Convocatoria
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Fase del proceso
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProcessPhase::class, 'process_phase_id');
    }

    /**
     * Unidad organizacional responsable
     */
    public function responsibleUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'responsible_unit_id');
    }

    /**
     * Estado detallado de la fase
     */
    public function phaseStatus(): HasOne
    {
        return $this->hasOne(JobPostingPhaseStatus::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', ScheduleStatusEnum::PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', ScheduleStatusEnum::IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', ScheduleStatusEnum::COMPLETED);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
                     ->where('status', ScheduleStatusEnum::PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', now())
                     ->where('status', '!=', ScheduleStatusEnum::COMPLETED);
    }

    /**
     * Verificar si está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === ScheduleStatusEnum::PENDING;
    }

    /**
     * Verificar si está en progreso
     */
    public function isInProgress(): bool
    {
        return $this->status === ScheduleStatusEnum::IN_PROGRESS;
    }

    /**
     * Verificar si está completada
     */
    public function isCompleted(): bool
    {
        return $this->status === ScheduleStatusEnum::COMPLETED;
    }

    /**
     * Verificar si está retrasada
     */
    public function isDelayed(): bool
    {
        return $this->end_date < now() && !$this->isCompleted();
    }

    /**
     * Iniciar fase
     */
    public function start(): void
    {
        $this->update([
            'status' => ScheduleStatusEnum::IN_PROGRESS,
            'actual_start_date' => now(),
        ]);

        // Disparar evento
        event(new \Modules\JobPosting\Events\PhaseStarted($this));
    }

    /**
     * Completar fase
     */
    public function complete(): void
    {
        $this->update([
            'status' => ScheduleStatusEnum::COMPLETED,
            'actual_end_date' => now(),
        ]);

        // Disparar evento
        event(new \Modules\JobPosting\Events\PhaseCompleted($this));
    }

    /**
     * Cancelar fase
     */
    public function cancel(): void
    {
        $this->update([
            'status' => ScheduleStatusEnum::CANCELLED,
        ]);
    }

    /**
     * Calcular duración en días
     */
    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Verificar si debe notificarse pronto
     */
    public function shouldNotify(): bool
    {
        if (!$this->notify_before || $this->notified_at) {
            return false;
        }

        $notifyDate = $this->start_date->subDays($this->notify_days_before);
        return now()->gte($notifyDate);
    }
}
