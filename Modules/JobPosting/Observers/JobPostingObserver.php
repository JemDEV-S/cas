<?php

namespace Modules\JobPosting\Observers;

use Modules\JobPosting\Entities\JobPosting;
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\JobPosting\Enums\ScheduleStatusEnum;
use Carbon\Carbon;

class JobPostingObserver
{
    /**
     * Handle the JobPosting "updated" event.
     * Se ejecuta cuando cambia el estado de una convocatoria
     */
    public function updated(JobPosting $jobPosting): void
    {
        // Detectar si acaba de cambiar a PUBLICADA
        if ($jobPosting->isDirty('status') && $jobPosting->status === JobPostingStatusEnum::PUBLICADA) {
            $this->autoStartFirstPhaseIfNeeded($jobPosting);
        }
    }

    /**
     * Auto-iniciar la primera fase si ya debería estar activa
     */
    protected function autoStartFirstPhaseIfNeeded(JobPosting $jobPosting): void
    {
        $now = Carbon::now();

        // Obtener la primera fase PENDING
        $firstPhase = $jobPosting->schedules()
            ->where('status', ScheduleStatusEnum::PENDING)
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->first();

        if (!$firstPhase) {
            return;
        }

        // Combinar fecha y hora
        $phaseStart = Carbon::parse($firstPhase->start_date);
        if ($firstPhase->start_time) {
            $timeParts = explode(':', $firstPhase->start_time);
            $phaseStart->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0));
        }

        // Si la fase ya debería haber comenzado, iniciarla automáticamente
        if ($now->gte($phaseStart)) {
            \Log::info("Auto-iniciando primera fase para convocatoria {$jobPosting->code}", [
                'job_posting_id' => $jobPosting->id,
                'phase' => $firstPhase->phase->name ?? 'N/A',
                'scheduled_start' => $phaseStart->toDateTimeString(),
            ]);

            $firstPhase->start();
        }
    }
}
