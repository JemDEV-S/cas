<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobPosting\Entities\JobPostingSchedule;
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\JobPosting\Enums\ScheduleStatusEnum;
use Modules\JobPosting\Events\PhaseDelayed;
use Carbon\Carbon;

class UpdateJobPostingPhasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobposting:update-phases {--dry-run : Simular sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza automáticamente los estados de las fases de convocatorias según fechas/horas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Modo DRY RUN - No se realizarán cambios en la base de datos');
        }

        $this->info('Iniciando actualización de fases...');

        $now = Carbon::now();
        $phasesStarted = 0;
        $phasesCompleted = 0;
        $phasesDelayed = 0;

        // Obtener convocatorias activas (PUBLICADA o EN_PROCESO)
        $activePostings = JobPosting::whereIn('status', [
            JobPostingStatusEnum::PUBLICADA,
            JobPostingStatusEnum::EN_PROCESO,
        ])
        ->with(['schedules.phase'])
        ->get();

        $this->info("Procesando {$activePostings->count()} convocatorias activas...");

        foreach ($activePostings as $posting) {
            $this->line("Convocatoria: {$posting->code} - {$posting->title}");

            foreach ($posting->schedules as $schedule) {
                $result = $this->processSchedule($schedule, $now, $isDryRun);

                if ($result === 'started') {
                    $phasesStarted++;
                } elseif ($result === 'completed') {
                    $phasesCompleted++;
                } elseif ($result === 'delayed') {
                    $phasesDelayed++;
                }
            }
        }

        $this->newLine();
        $this->info('Resumen de actualización:');
        $this->table(
            ['Acción', 'Cantidad'],
            [
                ['Fases iniciadas', $phasesStarted],
                ['Fases completadas', $phasesCompleted],
                ['Fases retrasadas', $phasesDelayed],
            ]
        );

        if ($isDryRun) {
            $this->warn('DRY RUN - No se realizaron cambios reales');
        }

        return Command::SUCCESS;
    }

    /**
     * Procesar un schedule individual
     */
    protected function processSchedule(JobPostingSchedule $schedule, Carbon $now, bool $isDryRun): ?string
    {
        $phaseName = $schedule->phase->name ?? 'Sin nombre';

        // Combinar fecha y hora para comparación precisa
        $scheduleStart = $this->combineDatetime($schedule->start_date, $schedule->start_time);
        $scheduleEnd = $this->combineDatetime($schedule->end_date, $schedule->end_time);

        // 1. Iniciar fases PENDING que ya deberían estar en progreso
        if ($schedule->status === ScheduleStatusEnum::PENDING && $now->gte($scheduleStart)) {
            $this->line("  → INICIANDO: {$phaseName} (desde {$scheduleStart->format('Y-m-d H:i')})");

            if (!$isDryRun) {
                $schedule->start();
            }

            return 'started';
        }

        // 2. Completar fases IN_PROGRESS que ya terminaron
        if ($schedule->status === ScheduleStatusEnum::IN_PROGRESS && $now->gt($scheduleEnd)) {
            $this->line("  → COMPLETANDO: {$phaseName} (terminó {$scheduleEnd->format('Y-m-d H:i')})");

            if (!$isDryRun) {
                $schedule->complete();

                // Auto-iniciar siguiente fase si existe y ya debe estar activa
                $this->autoStartNextPhase($schedule, $now, $isDryRun);
            }

            return 'completed';
        }

        // 3. Marcar como DELAYED fases que pasaron su end_date sin completarse
        if (in_array($schedule->status, [ScheduleStatusEnum::PENDING, ScheduleStatusEnum::IN_PROGRESS])
            && $now->gt($scheduleEnd)
            && $schedule->status !== ScheduleStatusEnum::DELAYED) {

            $this->warn("  ⚠ RETRASADA: {$phaseName} (debió terminar {$scheduleEnd->format('Y-m-d H:i')})");

            if (!$isDryRun) {
                $schedule->update(['status' => ScheduleStatusEnum::DELAYED]);
                event(new PhaseDelayed($schedule));
            }

            return 'delayed';
        }

        return null;
    }

    /**
     * Auto-iniciar siguiente fase si corresponde
     */
    protected function autoStartNextPhase(JobPostingSchedule $completedSchedule, Carbon $now, bool $isDryRun): void
    {
        $nextSchedule = JobPostingSchedule::where('job_posting_id', $completedSchedule->job_posting_id)
            ->where('status', ScheduleStatusEnum::PENDING)
            ->whereHas('phase', function($q) use ($completedSchedule) {
                $q->where('phase_number', '>', $completedSchedule->phase->phase_number);
            })
            ->orderBy('start_date')
            ->first();

        if ($nextSchedule) {
            $nextStart = $this->combineDatetime($nextSchedule->start_date, $nextSchedule->start_time);

            if ($now->gte($nextStart)) {
                $this->line("  → AUTO-INICIANDO siguiente fase: {$nextSchedule->phase->name}");

                if (!$isDryRun) {
                    $nextSchedule->start();
                }
            }
        }
    }

    /**
     * Combinar fecha y hora en un Carbon
     */
    protected function combineDatetime($date, $time = null): Carbon
    {
        $carbon = Carbon::parse($date);

        if ($time) {
            $timeParts = explode(':', $time);
            $carbon->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0), (int)($timeParts[2] ?? 0));
        }

        return $carbon;
    }
}
