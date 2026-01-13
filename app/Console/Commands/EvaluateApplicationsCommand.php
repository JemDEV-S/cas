<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        $force = $this->option('force');
        $evaluatorId = $this->option('evaluator');

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
            ->whereHas('vacancy.jobProfile.jobPosting', fn($q) => $q->where('id', $postingId));

        if (!$force) {
            $query->whereNull('eligibility_checked_at');
        }

        $applications = $query->with([
            'vacancy.jobProfile',
            'academics',
            'experiences',
            'trainings',
            'professionalRegistrations',
            'specialConditions'
        ])->get();

        if ($applications->isEmpty()) {
            $this->warn('⚠️  No hay postulaciones para evaluar.');
            return 0;
        }

        $this->info("📊 Total de postulaciones a evaluar: {$applications->count()}");
        $this->newLine();

        if ($dryRun) {
            $this->warn('🧪 MODO DRY-RUN: No se guardarán cambios');
            $this->newLine();
        }

        $progressBar = $this->output->createProgressBar($applications->count());
        $progressBar->start();

        $stats = [
            'eligible' => 0,
            'not_eligible' => 0,
            'errors' => 0,
        ];

        foreach ($applications as $application) {
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
                }

            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("\n❌ Error evaluando {$application->code}: {$e->getMessage()}");
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
}
