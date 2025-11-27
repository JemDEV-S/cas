<?php

namespace Modules\JobPosting\Services;

use Illuminate\Support\Facades\DB;
use Modules\JobPosting\Entities\{JobPosting, JobPostingSchedule, ProcessPhase};
use Carbon\Carbon;

class ScheduleService
{
    /**
     * Crear cronograma completo automáticamente
     */
    public function createFullSchedule(
        JobPosting $jobPosting,
        Carbon $startDate,
        ?string $defaultLocation = 'Portal Institucional'
    ): JobPosting {
        return DB::transaction(function() use ($jobPosting, $startDate, $defaultLocation) {
            // Obtener todas las fases activas
            $phases = ProcessPhase::active()->ordered()->get();

            $currentDate = $startDate->copy();

            foreach ($phases as $phase) {
                $endDate = $currentDate->copy()->addDays($phase->default_duration_days ?? 1);

                $jobPosting->schedules()->create([
                    'process_phase_id' => $phase->id,
                    'start_date' => $currentDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'location' => $defaultLocation,
                    'notes' => $phase->description,
                ]);

                // La siguiente fase comienza un día después de que termina la anterior
                $currentDate = $endDate->copy()->addDay();
            }

            return $jobPosting->load('schedules.phase');
        });
    }

    /**
     * Agregar fase individual al cronograma
     */
    public function addPhase(
        JobPosting $jobPosting,
        ProcessPhase $phase,
        array $data
    ): JobPostingSchedule {
        // Validar que no exista ya
        $exists = $jobPosting->schedules()
            ->where('process_phase_id', $phase->id)
            ->exists();

        if ($exists) {
            throw new \Exception("La fase '{$phase->name}' ya existe en el cronograma.");
        }

        return $jobPosting->schedules()->create(array_merge([
            'process_phase_id' => $phase->id,
        ], $data));
    }

    /**
     * Actualizar fase del cronograma
     */
    public function updateSchedule(
        JobPostingSchedule $schedule,
        array $data
    ): JobPostingSchedule {
        $schedule->update($data);
        return $schedule->fresh(['phase', 'responsibleUnit']);
    }

    /**
     * Eliminar fase del cronograma
     */
    public function removePhase(JobPostingSchedule $schedule): bool
    {
        return $schedule->delete();
    }

    /**
     * Iniciar fase
     */
    public function startPhase(JobPostingSchedule $schedule): JobPostingSchedule
    {
        if (!$schedule->isPending()) {
            throw new \Exception('Solo se pueden iniciar fases pendientes.');
        }

        $schedule->start();

        return $schedule->fresh();
    }

    /**
     * Completar fase
     */
    public function completePhase(JobPostingSchedule $schedule): JobPostingSchedule
    {
        if (!$schedule->isInProgress()) {
            throw new \Exception('Solo se pueden completar fases en progreso.');
        }

        $schedule->complete();

        // Auto-iniciar siguiente fase si existe
        $this->autoStartNextPhase($schedule);

        return $schedule->fresh();
    }

    /**
     * Auto-iniciar siguiente fase
     */
    protected function autoStartNextPhase(JobPostingSchedule $completedSchedule): void
    {
        $nextSchedule = JobPostingSchedule::where('job_posting_id', $completedSchedule->job_posting_id)
            ->whereHas('phase', function($q) use ($completedSchedule) {
                $q->where('order', '>', $completedSchedule->phase->order);
            })
            ->pending()
            ->orderBy('start_date')
            ->first();

        if ($nextSchedule && $nextSchedule->start_date <= now()) {
            $nextSchedule->start();
        }
    }

    /**
     * Recalcular fechas del cronograma
     */
    public function recalculateDates(
        JobPosting $jobPosting,
        Carbon $newStartDate
    ): JobPosting {
        return DB::transaction(function() use ($jobPosting, $newStartDate) {
            $schedules = $jobPosting->schedules()
                ->with('phase')
                ->orderBy('start_date')
                ->get();

            $currentDate = $newStartDate->copy();

            foreach ($schedules as $schedule) {
                $duration = $schedule->phase->default_duration_days ?? 
                           $schedule->start_date->diffInDays($schedule->end_date);

                $endDate = $currentDate->copy()->addDays($duration);

                $schedule->update([
                    'start_date' => $currentDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ]);

                $currentDate = $endDate->copy()->addDay();
            }

            return $jobPosting->fresh('schedules.phase');
        });
    }

    /**
     * Obtener fase actual
     */
    public function getCurrentPhase(JobPosting $jobPosting): ?JobPostingSchedule
    {
        return $jobPosting->schedules()
            ->inProgress()
            ->with(['phase', 'responsibleUnit'])
            ->first();
    }

    /**
     * Obtener próxima fase
     */
    public function getNextPhase(JobPosting $jobPosting): ?JobPostingSchedule
    {
        return $jobPosting->schedules()
            ->pending()
            ->orderBy('start_date')
            ->with(['phase', 'responsibleUnit'])
            ->first();
    }

    /**
     * Obtener fases completadas
     */
    public function getCompletedPhases(JobPosting $jobPosting)
    {
        return $jobPosting->schedules()
            ->completed()
            ->with(['phase', 'responsibleUnit'])
            ->get();
    }

    /**
     * Verificar si el cronograma está completo
     */
    public function isScheduleComplete(JobPosting $jobPosting): bool
    {
        $activePhases = ProcessPhase::active()->count();
        $scheduledPhases = $jobPosting->schedules()->count();

        return $scheduledPhases >= $activePhases;
    }

    /**
     * Obtener progreso del cronograma
     */
    public function getScheduleProgress(JobPosting $jobPosting): array
    {
        $total = $jobPosting->schedules()->count();
        
        if ($total === 0) {
            return [
                'total' => 0,
                'completed' => 0,
                'in_progress' => 0,
                'pending' => 0,
                'percentage' => 0,
            ];
        }

        $completed = $jobPosting->schedules()->completed()->count();
        $inProgress = $jobPosting->schedules()->inProgress()->count();
        $pending = $jobPosting->schedules()->pending()->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'percentage' => round(($completed / $total) * 100, 2),
        ];
    }

    /**
     * Detectar fases retrasadas
     */
    public function getDelayedPhases(JobPosting $jobPosting)
    {
        return $jobPosting->schedules()
            ->where('end_date', '<', now())
            ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
            ->with(['phase', 'responsibleUnit'])
            ->get();
    }

    /**
     * Obtener timeline para visualización
     */
    public function getTimeline(JobPosting $jobPosting): array
    {
        $schedules = $jobPosting->schedules()
            ->with(['phase', 'responsibleUnit'])
            ->orderBy('start_date')
            ->get();

        return $schedules->map(function($schedule) {
            return [
                'id' => $schedule->id,
                'phase_name' => $schedule->phase->name,
                'phase_number' => $schedule->phase->phase_number,
                'start_date' => $schedule->start_date->format('Y-m-d'),
                'end_date' => $schedule->end_date->format('Y-m-d'),
                'status' => $schedule->status->value,
                'status_label' => $schedule->status->label(),
                'status_color' => $schedule->status->color(),
                'location' => $schedule->location,
                'responsible_unit' => $schedule->responsibleUnit?->name,
                'is_delayed' => $schedule->isDelayed(),
                'duration_days' => $schedule->getDurationInDays(),
            ];
        })->toArray();
    }

    /**
     * Notificar fases próximas
     */
    public function notifyUpcomingPhases(): int
    {
        $schedules = JobPostingSchedule::query()
            ->where('notify_before', true)
            ->whereNull('notified_at')
            ->pending()
            ->get();

        $notifiedCount = 0;

        foreach ($schedules as $schedule) {
            if ($schedule->shouldNotify()) {
                // Aquí se enviaría la notificación
                // event(new PhaseUpcomingEvent($schedule));
                
                $schedule->update(['notified_at' => now()]);
                $notifiedCount++;
            }
        }

        return $notifiedCount;
    }

    /**
     * Validar solapamiento de fechas
     */
    public function validateNoOverlap(
        JobPosting $jobPosting,
        Carbon $startDate,
        Carbon $endDate,
        ?string $excludeScheduleId = null
    ): bool {
        $query = $jobPosting->schedules()
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return !$query->exists();
    }
}