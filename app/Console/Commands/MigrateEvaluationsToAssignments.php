<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Evaluation\Entities\Evaluation;
use Modules\Evaluation\Entities\EvaluatorAssignment;
use Illuminate\Support\Facades\DB;

class MigrateEvaluationsToAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluations:migrate-to-assignments {--dry-run : Ejecutar sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra evaluaciones existentes al nuevo sistema de asignaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo DRY RUN - No se harán cambios en la base de datos');
        }

        // Obtener evaluaciones sin evaluator_assignment_id
        $evaluations = Evaluation::whereNull('evaluator_assignment_id')
            ->whereNotNull('evaluator_id')
            ->whereNotNull('application_id')
            ->whereNotNull('phase_id')
            ->get();

        if ($evaluations->isEmpty()) {
            $this->info('No hay evaluaciones pendientes de migrar.');
            return 0;
        }

        $this->info("Encontradas {$evaluations->count()} evaluaciones para migrar.");

        $migrated = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($evaluations->count());
        $progressBar->start();

        foreach ($evaluations as $evaluation) {
            try {
                DB::beginTransaction();

                // Buscar si ya existe una asignación
                $assignment = EvaluatorAssignment::where('user_id', $evaluation->evaluator_id)
                    ->where('application_id', $evaluation->application_id)
                    ->where('phase_id', $evaluation->phase_id)
                    ->first();

                // Si no existe, crear la asignación
                if (!$assignment) {
                    if (!$dryRun) {
                        $assignment = EvaluatorAssignment::create([
                            'user_id' => $evaluation->evaluator_id,
                            'application_id' => $evaluation->application_id,
                            'phase_id' => $evaluation->phase_id,
                            'job_posting_id' => $evaluation->job_posting_id,
                            'assignment_type' => 'MANUAL', // Asumimos que las existentes son manuales
                            'status' => $evaluation->isCompleted() ? 'COMPLETED' : 'IN_PROGRESS',
                            'deadline_at' => $evaluation->deadline_at,
                        ]);
                    } else {
                        $this->line("\n[DRY RUN] Crearía asignación para Evaluación ID: {$evaluation->id}");
                    }
                }

                // Vincular la evaluación con la asignación
                if (!$dryRun && $assignment) {
                    $evaluation->evaluator_assignment_id = $assignment->id;
                    $evaluation->save();
                }

                DB::commit();
                $migrated++;

            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->error("\nError migrando evaluación ID {$evaluation->id}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Migración completada:");
        $this->table(
            ['Estado', 'Cantidad'],
            [
                ['Migradas exitosamente', $migrated],
                ['Errores', $errors],
            ]
        );

        if ($dryRun) {
            $this->warn('Esto fue una simulación. Ejecuta sin --dry-run para aplicar los cambios.');
        }

        return 0;
    }
}
