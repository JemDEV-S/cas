<?php

namespace Modules\JobPosting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\{HasUuid, HasMetadata};
use Modules\JobPosting\Enums\ScheduleStatusEnum;
use Modules\User\Entities\User;

class JobPostingPhaseStatus extends Model
{
    use HasUuid, HasMetadata, SoftDeletes;

    protected $fillable = [
        'job_posting_id',
        'job_posting_schedule_id',
        'status',
        'progress_percentage',
        'started_at',
        'completed_at',
        'started_by',
        'completed_by',
        'observations',
        'checklist',
        'metadata',
    ];

    protected $casts = [
        'status' => ScheduleStatusEnum::class,
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'checklist' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Convocatoria
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Cronograma de fase
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(JobPostingSchedule::class, 'job_posting_schedule_id');
    }

    /**
     * Usuario que inició
     */
    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    /**
     * Usuario que completó
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Actualizar progreso
     */
    public function updateProgress(int $percentage): void
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
        ]);
    }

    /**
     * Marcar item de checklist
     */
    public function checkItem(string $item): void
    {
        $checklist = $this->checklist ?? [];
        $checklist[$item] = true;
        $this->update(['checklist' => $checklist]);
    }

    /**
     * Verificar si está completo el checklist
     */
    public function isChecklistComplete(): bool
    {
        if (!$this->checklist) {
            return false;
        }

        return !in_array(false, $this->checklist, true);
    }
}