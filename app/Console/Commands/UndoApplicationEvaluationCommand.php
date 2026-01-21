<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;

class UndoApplicationEvaluationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:undo-evaluation
                            {application_id : ID o código de la postulación}
                            {--force : No pedir confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deshace la evaluación de una postulación y la devuelve a estado SUBMITTED';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $applicationIdentifier = $this->argument('application_id');
        $force = $this->option('force');

        $this->info('🔍 Buscando postulación...');
        $this->newLine();

        // Buscar por ID (UUID) o por código
        $application = Application::where('id', $applicationIdentifier)
            ->orWhere('code', $applicationIdentifier)
            ->with([
                'jobProfile.jobPosting',
                'applicant',
            ])
            ->first();

        if (!$application) {
            $this->error("❌ No se encontró la postulación: {$applicationIdentifier}");
            return Command::FAILURE;
        }

        // Mostrar información de la postulación
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  INFORMACIÓN DE LA POSTULACIÓN');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("  ID:              {$application->id}");
        $this->line("  Código:          {$application->code}");
        $this->line("  Postulante:      {$application->full_name}");
        $this->line("  DNI:             {$application->dni}");
        $this->line("  Estado actual:   {$application->status->value}");
        $this->line("  Perfil:          " . ($application->jobProfile?->profile_name ?? 'N/A'));
        $this->line("  Convocatoria:    " . ($application->jobProfile?->jobPosting?->title ?? 'N/A'));

        if ($application->eligibility_checked_at) {
            $this->line("  Evaluado el:     " . $application->eligibility_checked_at->format('d/m/Y H:i:s'));
            $this->line("  Es elegible:     " . ($application->is_eligible ? 'SÍ (APTO)' : 'NO (NO APTO)'));
            if ($application->ineligibility_reason) {
                $this->line("  Razón no apto:   " . str_replace("\n", ", ", $application->ineligibility_reason));
            }
        }
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Verificar si está en un estado que permite deshacer
        $allowedStatuses = [
            ApplicationStatus::ELIGIBLE,
            ApplicationStatus::NOT_ELIGIBLE,
        ];

        if (!in_array($application->status, $allowedStatuses)) {
            $this->error("❌ La postulación está en estado '{$application->status->value}' y no puede ser revertida.");
            $this->comment("   Solo se pueden revertir postulaciones en estado ELIGIBLE o NOT_ELIGIBLE.");
            return Command::FAILURE;
        }

        // Verificar si tiene datos de evaluación
        if (!$application->eligibility_checked_at) {
            $this->warn('⚠️  Esta postulación no tiene datos de evaluación registrados.');
            if (!$force && !$this->confirm('¿Desea continuar de todos modos?')) {
                return Command::SUCCESS;
            }
        }

        // Confirmar acción
        if (!$force) {
            $this->warn('⚠️  Esta acción realizará los siguientes cambios:');
            $this->line('   1. Cambiará el estado de la postulación a SUBMITTED');
            $this->line('   2. Limpiará los campos de elegibilidad (is_eligible, eligibility_checked_at, etc.)');
            $this->line('   3. Eliminará las evaluaciones automáticas (ApplicationEvaluation)');
            $this->line('   4. Eliminará las evaluaciones del módulo Evaluation (fase 4)');
            $this->line('   5. Registrará la acción en el historial');
            $this->newLine();

            if (!$this->confirm('¿Está seguro de que desea deshacer la evaluación de esta postulación?')) {
                $this->info('Operación cancelada.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('🔄 Deshaciendo evaluación...');

        try {
            \DB::beginTransaction();

            // 1. Contar registros a eliminar para el resumen
            $applicationEvaluationsCount = \Modules\Application\Entities\ApplicationEvaluation::where('application_id', $application->id)->count();

            $evaluationsCount = \Modules\Evaluation\Entities\Evaluation::where('application_id', $application->id)->count();

            $evaluationDetailsCount = 0;
            $evaluationHistoryCount = 0;

            // Obtener IDs de evaluaciones para eliminar detalles e historial
            $evaluationIds = \Modules\Evaluation\Entities\Evaluation::where('application_id', $application->id)
                ->pluck('id')
                ->toArray();

            if (!empty($evaluationIds)) {
                $evaluationDetailsCount = \Modules\Evaluation\Entities\EvaluationDetail::whereIn('evaluation_id', $evaluationIds)->count();
                $evaluationHistoryCount = \Modules\Evaluation\Entities\EvaluationHistory::whereIn('evaluation_id', $evaluationIds)->count();
            }

            // 2. Eliminar ApplicationEvaluations
            \Modules\Application\Entities\ApplicationEvaluation::where('application_id', $application->id)->delete();
            $this->line("   ✓ Eliminadas {$applicationEvaluationsCount} evaluaciones automáticas (ApplicationEvaluation)");

            // 3. Eliminar detalles de evaluación del módulo Evaluation (PRIMERO - por FK)
            if (!empty($evaluationIds)) {
                // Usar DB::table para evitar problemas con SoftDeletes y asegurar eliminación real
                \DB::table('evaluation_details')->whereIn('evaluation_id', $evaluationIds)->delete();
                $this->line("   ✓ Eliminados {$evaluationDetailsCount} detalles de evaluación (EvaluationDetail)");

                // 4. Eliminar historial de evaluación
                \DB::table('evaluation_history')->whereIn('evaluation_id', $evaluationIds)->delete();
                $this->line("   ✓ Eliminados {$evaluationHistoryCount} registros de historial (EvaluationHistory)");

                // 5. Eliminar evaluaciones (usar DB::table para forzar eliminación real)
                \DB::table('evaluations')->whereIn('id', $evaluationIds)->delete();
                $this->line("   ✓ Eliminadas {$evaluationsCount} evaluaciones (Evaluation)");
            }

            // 6. Actualizar la postulación
            $previousStatus = $application->status->value;
            $wasEligible = $application->is_eligible;

            $application->update([
                'status' => ApplicationStatus::SUBMITTED,
                'is_eligible' => null,
                'eligibility_checked_at' => null,
                'eligibility_checked_by' => null,
                'ineligibility_reason' => null,
            ]);
            $this->line("   ✓ Estado actualizado de {$previousStatus} a SUBMITTED");

            // 7. Registrar en el historial de la aplicación
            \Modules\Application\Entities\ApplicationHistory::create([
                'application_id' => $application->id,
                'event_type' => 'EVALUATION_UNDONE',
                'description' => sprintf(
                    'Evaluación revertida. Estado anterior: %s (%s). Postulación devuelta a SUBMITTED.',
                    $previousStatus,
                    $wasEligible ? 'APTO' : 'NO APTO'
                ),
                'performed_by' => auth()->id() ?? \Modules\User\Entities\User::first()?->id,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'CLI',
                'metadata' => [
                    'previous_status' => $previousStatus,
                    'previous_is_eligible' => $wasEligible,
                    'application_evaluations_deleted' => $applicationEvaluationsCount,
                    'evaluations_deleted' => $evaluationsCount,
                    'evaluation_details_deleted' => $evaluationDetailsCount,
                    'command' => 'applications:undo-evaluation',
                    'executed_at' => now()->toIso8601String(),
                ],
                'performed_at' => now(),
            ]);
            $this->line("   ✓ Registrado en historial de la postulación");

            \DB::commit();

            $this->newLine();
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('  ✅ EVALUACIÓN REVERTIDA EXITOSAMENTE');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line("  Postulación:     {$application->code}");
            $this->line("  Estado anterior: {$previousStatus}");
            $this->line("  Estado actual:   SUBMITTED");
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            $this->comment('💡 La postulación ahora puede ser evaluada nuevamente con:');
            $this->comment("   php artisan applications:evaluate {posting_id}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            \DB::rollBack();

            $this->error("❌ Error al deshacer la evaluación: {$e->getMessage()}");
            \Log::error('Error en UndoApplicationEvaluationCommand', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
