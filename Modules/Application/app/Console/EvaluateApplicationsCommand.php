<?php

namespace Modules\Application\Console;

use Illuminate\Console\Command;
use Modules\Application\Services\AutoGraderService;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\JobPosting\Entities\JobPosting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluateApplicationsCommand extends Command
{
    protected $signature = 'applications:evaluate
                            {posting : ID de la convocatoria}
                            {--dry-run : Simular sin guardar cambios}
                            {--user= : ID del usuario que ejecuta (default: system)}';

    protected $description = 'Evaluar elegibilidad automática de postulaciones de una convocatoria';

    public function __construct(
        private AutoGraderService $autoGrader
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $postingId = $this->argument('posting');
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user') ?? 'system';

        $this->info("🚀 Iniciando evaluación automática para convocatoria: {$postingId}");

        // 1. Validar convocatoria
        try {
            $posting = JobPosting::findOrFail($postingId);
        } catch (\Exception $e) {
            $this->error("❌ Convocatoria no encontrada: {$postingId}");
            return Command::FAILURE;
        }

        // 2. Validar fase
        $currentPhase = $posting->getCurrentPhase();
        if (!$currentPhase || $currentPhase->phase->code !== 'PHASE_03_REGISTRATION') {
            $this->error('❌ La evaluación solo puede ejecutarse en la Fase 3 (Registro)');
            $this->info('Fase actual: ' . ($currentPhase->phase->name ?? 'No definida'));
            return Command::FAILURE;
        }

        // 3. Obtener postulaciones
        $applications = Application::where('status', ApplicationStatus::SUBMITTED)
            ->whereHas('vacancy.jobProfileRequest.jobPosting', fn($q) => $q->where('id', $postingId))
            ->with([
                'academics.career',
                'experiences',
                'trainings',
                'professionalRegistrations',
                'knowledge',
                'vacancy.jobProfileRequest.careers'
            ])
            ->get();

        if ($applications->isEmpty()) {
            $this->warn('⚠️  No hay postulaciones para evaluar');
            return Command::SUCCESS;
        }

        $this->info("📊 Total de postulaciones a evaluar: {$applications->count()}");

        if ($dryRun) {
            $this->warn('🔍 Modo DRY RUN activado - No se guardarán cambios');
        }

        // 4. Crear progress bar
        $bar = $this->output->createProgressBar($applications->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $stats = [
            'total' => $applications->count(),
            'eligible' => 0,
            'not_eligible' => 0,
            'errors' => 0,
            'details' => [],
        ];

        // 5. Evaluar cada postulación
        foreach ($applications as $application) {
            $bar->setMessage("Evaluando: {$application->full_name}");

            try {
                $result = $this->autoGrader->evaluateEligibility($application);

                if (!$dryRun) {
                    $this->autoGrader->applyAutoGrading($application, $userId);
                }

                if ($result['is_eligible']) {
                    $stats['eligible']++;
                    $status = '✅ APTO';
                } else {
                    $stats['not_eligible']++;
                    $status = '❌ NO APTO';
                }

                $stats['details'][] = [
                    'application_code' => $application->code,
                    'applicant_name' => $application->full_name,
                    'dni' => $application->dni,
                    'result' => $status,
                    'reasons' => $result['reasons'],
                ];

            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("\n❌ Error evaluando {$application->full_name}: {$e->getMessage()}");

                Log::error('Error evaluando postulación', [
                    'application_id' => $application->id,
                    'application_code' => $application->code,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // 6. Mostrar resumen
        $this->info('📈 Resumen de Evaluación:');
        $this->table(
            ['Métrica', 'Cantidad', 'Porcentaje'],
            [
                ['Total evaluadas', $stats['total'], '100%'],
                ['✅ APTOS', $stats['eligible'], round(($stats['eligible'] / $stats['total']) * 100, 2) . '%'],
                ['❌ NO APTOS', $stats['not_eligible'], round(($stats['not_eligible'] / $stats['total']) * 100, 2) . '%'],
                ['⚠️  Errores', $stats['errors'], $stats['errors'] > 0 ? round(($stats['errors'] / $stats['total']) * 100, 2) . '%' : '0%'],
            ]
        );

        // 7. Mostrar detalles de NO APTOS
        if ($stats['not_eligible'] > 0) {
            $this->newLine();
            $this->info('❌ Postulantes NO APTOS:');

            $notEligibleDetails = array_filter($stats['details'], fn($d) => str_contains($d['result'], 'NO APTO'));

            foreach ($notEligibleDetails as $detail) {
                $this->line("• {$detail['applicant_name']} (DNI: {$detail['dni']})");
                foreach ($detail['reasons'] as $reason) {
                    $this->line("  - {$reason}");
                }
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 Modo DRY RUN: No se guardaron cambios');
            $this->info('Ejecute sin --dry-run para guardar los resultados');
        } else {
            $this->info('✅ Evaluación completada exitosamente');
            $this->info('Los resultados han sido guardados en la base de datos');
        }

        return Command::SUCCESS;
    }
}
