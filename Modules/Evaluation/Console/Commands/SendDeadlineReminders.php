<?php

namespace Modules\Evaluation\Console\Commands;

use Illuminate\Console\Command;
use Modules\Evaluation\Entities\Evaluation;
use Modules\Evaluation\Events\EvaluationDeadlineApproaching;
use Carbon\Carbon;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluation:send-deadline-reminders 
                            {--days=2 : Días antes de la fecha límite para enviar recordatorio}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar recordatorios de evaluaciones próximas a vencer';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = Carbon::now()->addDays($days)->endOfDay();

        $this->info("Buscando evaluaciones que vencen en aproximadamente {$days} días...");

        // Obtener evaluaciones pendientes próximas a vencer
        $evaluations = Evaluation::pending()
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<=', $targetDate)
            ->where('deadline_at', '>=', Carbon::now())
            ->with('evaluator')
            ->get();

        if ($evaluations->isEmpty()) {
            $this->info('No hay evaluaciones próximas a vencer.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$evaluations->count()} evaluaciones.");

        $bar = $this->output->createProgressBar($evaluations->count());
        $bar->start();

        $sent = 0;
        foreach ($evaluations as $evaluation) {
            try {
                $daysRemaining = Carbon::now()->diffInDays($evaluation->deadline_at, false);
                
                // Disparar evento
                event(new EvaluationDeadlineApproaching($evaluation, (int) ceil($daysRemaining)));
                
                $sent++;
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\nError procesando evaluación #{$evaluation->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Recordatorios enviados: {$sent}/{$evaluations->count()}");

        return Command::SUCCESS;
    }
}