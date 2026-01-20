<?php

namespace App\Console\Commands;

use App\Exports\DryRunEvaluationExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\Application\Services\AutoGraderService;
use Modules\JobPosting\Entities\JobPosting;

class EvaluateApplicationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applications:evaluate
                            {posting_id : ID de la convocatoria}
                            {--dry-run : Simular sin guardar cambios}
                            {--export : Exportar resultados a Excel (solo con --dry-run)}
                            {--output= : Ruta del archivo Excel de salida (por defecto: storage/app/evaluations/)}
                            {--force : Forzar evaluación incluso si ya fueron evaluadas}
                            {--evaluator= : UUID del usuario evaluador (por defecto: primer admin)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluar automáticamente la elegibilidad de postulaciones presentadas';

    protected AutoGraderService $autoGrader;

    public function __construct(AutoGraderService $autoGrader)
    {
        parent::__construct();
        $this->autoGrader = $autoGrader;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $postingId = $this->argument('posting_id');
        $dryRun = $this->option('dry-run');
        $export = $this->option('export');
        $outputPath = $this->option('output');
        $force = $this->option('force');
        $evaluatorId = $this->option('evaluator');

        // Validar que --export solo funciona con --dry-run
        if ($export && !$dryRun) {
            $this->error('❌ La opción --export solo está disponible con --dry-run');
            return 1;
        }

        // Validar que la convocatoria existe
        $posting = JobPosting::find($postingId);
        if (!$posting) {
            $this->error("❌ Convocatoria no encontrada: {$postingId}");
            return 1;
        }

        $this->info("🔍 Evaluando postulaciones de: {$posting->code} - {$posting->title}");
        $this->newLine();

        // Obtener postulaciones a evaluar
        $query = Application::where('status', ApplicationStatus::SUBMITTED)
            ->whereHas('jobProfile.jobPosting', fn($q) => $q->where('id', $postingId));

        if (!$force) {
            $query->whereNull('eligibility_checked_at');
        }

        $applications = $query->with([
            'jobProfile',
            'academics.career',
            'experiences',
            'trainings',
            'professionalRegistrations',
            'specialConditions',
            'applicant',
        ])->get();

        if ($applications->isEmpty()) {
            $this->warn('⚠️  No hay postulaciones para evaluar.');
            return 0;
        }

        $this->info("📊 Total de postulaciones a evaluar: {$applications->count()}");
        $this->newLine();

        if ($dryRun) {
            $this->warn('🧪 MODO DRY-RUN: No se guardarán cambios');
            if ($export) {
                $this->info('📄 Se generará reporte Excel con resultados detallados');
            }
            $this->newLine();
        }

        $progressBar = $this->output->createProgressBar($applications->count());
        $progressBar->start();

        $stats = [
            'eligible' => 0,
            'not_eligible' => 0,
            'errors' => 0,
        ];

        // Colección para almacenar resultados detallados (para export)
        $evaluationResults = collect();
        $rowNumber = 0;

        foreach ($applications as $application) {
            $rowNumber++;
            try {
                if (!$dryRun) {
                    // Usar nuevo método integrado con módulo Evaluation
                    // Determinar el evaluador: opción --evaluator, usuario autenticado, o primer admin
                    $evaluatedBy = $evaluatorId ?? auth()->id() ?? \App\Models\User::role('admin')->first()->id;

                    $evaluation = $this->autoGrader->applyAutoGradingWithEvaluationModule(
                        $application,
                        $evaluatedBy
                    );

                    // Contar resultados
                    if ($evaluation->isCompleted() && $application->is_eligible) {
                        $stats['eligible']++;
                    } else {
                        $stats['not_eligible']++;
                    }
                } else {
                    // En modo dry-run, solo evaluar sin guardar
                    $result = $this->autoGrader->evaluateEligibility($application);

                    if ($result['is_eligible']) {
                        $stats['eligible']++;
                    } else {
                        $stats['not_eligible']++;
                    }

                    // Guardar resultado detallado para exportación
                    if ($export) {
                        $evaluationResults->push($this->formatEvaluationResult(
                            $rowNumber,
                            $application,
                            $result
                        ));
                    }
                }

            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("\n❌ Error evaluando {$application->code}: {$e->getMessage()}");

                // Agregar error al reporte
                if ($export && $dryRun) {
                    $evaluationResults->push([
                        'number' => $rowNumber,
                        'application_code' => $application->code ?? 'N/A',
                        'dni' => $application->dni ?? 'N/A',
                        'full_name' => $application->full_name ?? 'N/A',
                        'job_profile' => $application->jobProfile?->title ?? 'N/A',
                        'result' => 'ERROR',
                        'academics_status' => 'ERROR',
                        'academics_detail' => $e->getMessage(),
                        'general_exp_status' => '-',
                        'general_exp_detail' => '-',
                        'specific_exp_status' => '-',
                        'specific_exp_detail' => '-',
                        'colegiatura_status' => '-',
                        'colegiatura_detail' => '-',
                        'courses_status' => '-',
                        'courses_detail' => '-',
                        'reasons' => 'Error durante evaluación: ' . $e->getMessage(),
                    ]);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar estadísticas
        $this->info('═══════════════════════════════════════════');
        $this->info('📈 RESULTADOS DE LA EVALUACIÓN');
        $this->info('═══════════════════════════════════════════');
        $this->line("✅ APTOS:        {$stats['eligible']}");
        $this->line("❌ NO APTOS:     {$stats['not_eligible']}");
        if ($stats['errors'] > 0) {
            $this->error("⚠️  ERRORES:      {$stats['errors']}");
        }
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        // Exportar a Excel si se solicitó
        if ($dryRun && $export && $evaluationResults->isNotEmpty()) {
            $filePath = $this->exportToExcel($evaluationResults, $posting, $outputPath);
            $this->newLine();
            $this->info("📊 Reporte Excel generado: {$filePath}");
        }

        if ($dryRun) {
            $this->warn('🧪 Los cambios NO fueron guardados (modo dry-run)');
        } else {
            $this->info('💾 Evaluación completada y guardada exitosamente');
            $this->newLine();
            $this->comment('⚠️  IMPORTANTE: Los resultados aún NO están publicados.');
            $this->comment('   Para publicarlos a los postulantes, un administrador debe:');
            $this->comment('   1. Revisar los resultados en el dashboard');
            $this->comment('   2. Hacer clic en "Publicar Resultados de Fase 4"');
        }

        return 0;
    }

    /**
     * Formatear resultado de evaluación para el Excel
     */
    private function formatEvaluationResult(int $number, Application $application, array $result): array
    {
        $details = $result['details'] ?? [];

        return [
            'number' => $number,
            'application_code' => $application->code ?? 'N/A',
            'dni' => $application->dni ?? 'N/A',
            'full_name' => $application->full_name ?? 'N/A',
            'job_profile' => $application->jobProfile?->title ?? 'N/A',
            'result' => $result['is_eligible'] ? 'APTO' : 'NO APTO',
            'academics_status' => $this->formatStatus($details['academics'] ?? null),
            'academics_detail' => $this->formatDetail($details['academics'] ?? null),
            'general_exp_status' => $this->formatStatus($details['general_experience'] ?? null),
            'general_exp_detail' => $this->formatExperienceDetail($details['general_experience'] ?? null),
            'specific_exp_status' => $this->formatStatus($details['specific_experience'] ?? null),
            'specific_exp_detail' => $this->formatExperienceDetail($details['specific_experience'] ?? null),
            'colegiatura_status' => $this->formatStatus($details['professional_registry'] ?? null),
            'colegiatura_detail' => $this->formatDetail($details['professional_registry'] ?? null),
            'courses_status' => $this->formatStatus($details['required_courses'] ?? null),
            'courses_detail' => $this->formatCoursesDetail($details['required_courses'] ?? null),
            'reasons' => !$result['is_eligible'] ? implode("\n", $result['reasons'] ?? []) : '-',
        ];
    }

    /**
     * Obtener nombre completo del postulante
     */
    private function getApplicantFullName(Application $application): string
    {
        $applicant = $application->applicant;
        if (!$applicant) {
            return 'N/A';
        }

        return trim(sprintf(
            '%s %s, %s',
            $applicant->paternal_surname ?? '',
            $applicant->maternal_surname ?? '',
            $applicant->first_name ?? ''
        ));
    }

    /**
     * Formatear estado de criterio
     */
    private function formatStatus(?array $detail): string
    {
        if ($detail === null) {
            return 'N/A';
        }

        return ($detail['passed'] ?? false) ? 'CUMPLE' : 'NO CUMPLE';
    }

    /**
     * Formatear detalle genérico
     */
    private function formatDetail(?array $detail): string
    {
        if ($detail === null) {
            return 'No evaluado';
        }

        return $detail['reason'] ?? 'Sin detalle';
    }

    /**
     * Formatear detalle de experiencia
     */
    private function formatExperienceDetail(?array $detail): string
    {
        if ($detail === null) {
            return 'No evaluado';
        }

        $required = $detail['required'] ?? 'Sin experiencia';
        $achieved = $detail['achieved'] ?? 'Sin experiencia';
        $reason = $detail['reason'] ?? '';

        return sprintf(
            "Requerido: %s | Acreditado: %s\n%s",
            $required,
            $achieved,
            $reason
        );
    }

    /**
     * Formatear detalle de cursos
     */
    private function formatCoursesDetail(?array $detail): string
    {
        if ($detail === null) {
            return 'No evaluado';
        }

        $lines = [];

        if (!empty($detail['required'])) {
            $lines[] = 'Requeridos: ' . implode(', ', $detail['required']);
        }

        if (!empty($detail['found'])) {
            $lines[] = 'Encontrados: ' . implode(', ', $detail['found']);
        }

        if (!empty($detail['missing'])) {
            $lines[] = 'Faltantes: ' . implode(', ', $detail['missing']);
        }

        $lines[] = $detail['reason'] ?? '';

        return implode("\n", array_filter($lines));
    }

    /**
     * Exportar resultados a Excel
     */
    private function exportToExcel($evaluationResults, JobPosting $posting, ?string $outputPath): string
    {
        // Crear directorio si no existe
        $directory = $outputPath ?? storage_path('app/evaluations');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generar nombre de archivo
        $timestamp = now()->format('Y-m-d_His');
        $fileName = sprintf(
            'evaluacion_dryrun_%s_%s.xlsx',
            str_replace(['/', '\\', ' '], '_', $posting->code),
            $timestamp
        );

        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

        // Crear y guardar Excel
        $export = new DryRunEvaluationExport(
            $evaluationResults,
            $posting->code,
            $posting->title
        );

        Excel::store($export, 'evaluations/' . $fileName, 'local');

        return storage_path('app/evaluations/' . $fileName);
    }
}
