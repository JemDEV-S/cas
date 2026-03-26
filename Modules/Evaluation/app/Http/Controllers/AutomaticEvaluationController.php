<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\Application\Jobs\EvaluateApplicationBatch;
use Modules\Evaluation\Entities\Evaluation;
use Modules\Evaluation\Policies\AutomaticEvaluationPolicy;
use Modules\JobPosting\Entities\JobPosting;
use Modules\JobPosting\Entities\ProcessPhase;

class AutomaticEvaluationController extends Controller
{
    /**
     * Display the automatic evaluation interface.
     */
    public function index()
    {
        // Verificar autorización
        Gate::authorize('viewAny', AutomaticEvaluationPolicy::class);

        // Obtener convocatorias activas con postulaciones presentadas
        $jobPostings = JobPosting::query()
            ->with(['jobProfiles.applications' => function ($query) {
                $query->whereIn('status', [
                    ApplicationStatus::SUBMITTED,
                    ApplicationStatus::ELIGIBLE,
                    ApplicationStatus::NOT_ELIGIBLE
                ]);
            }])
            ->published()
            ->whereHas('jobProfiles.applications', function ($query) {
                $query->where('status', ApplicationStatus::SUBMITTED);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener estadísticas de evaluaciones automáticas
        $stats = [
            'total_job_postings' => $jobPostings->count(),
            'total_pending' => 0,
            'total_evaluated' => 0,
        ];

        foreach ($jobPostings as $posting) {
            foreach ($posting->jobProfiles as $jobProfile) {
                $stats['total_pending'] += $jobProfile->applications
                    ->where('status', ApplicationStatus::SUBMITTED)
                    ->whereNull('eligibility_checked_at')
                    ->count();

                $stats['total_evaluated'] += $jobProfile->applications
                    ->whereIn('status', [ApplicationStatus::ELIGIBLE, ApplicationStatus::NOT_ELIGIBLE])
                    ->whereNotNull('eligibility_checked_at')
                    ->count();
            }
        }

        return view('evaluation::automatic.index', compact('jobPostings', 'stats'));
    }

    /**
     * Show details for a specific job posting.
     */
    public function show(string $id)
    {
        Gate::authorize('viewAny', AutomaticEvaluationPolicy::class);

        $jobPosting = JobPosting::with([
            'jobProfiles.applications' => function ($query) {
                $query->whereIn('status', [
                    ApplicationStatus::SUBMITTED,
                    ApplicationStatus::ELIGIBLE,
                    ApplicationStatus::NOT_ELIGIBLE
                ])->orderBy('created_at', 'desc');
            },
            'jobProfiles.applications.evaluations' => function ($query) {
                $query->whereHas('phase', fn($q) => $q->where('code', 'PHASE_04_ELIGIBLE_PUB'));
            }
        ])->findOrFail($id);

        // Calcular estadísticas
        $allApplications = $jobPosting->jobProfiles->flatMap->applications;

        $stats = [
            'total' => $allApplications->count(),
            'pending' => $allApplications->where('status', ApplicationStatus::SUBMITTED)
                ->whereNull('eligibility_checked_at')->count(),
            'eligible' => $allApplications->where('status', ApplicationStatus::ELIGIBLE)->count(),
            'not_eligible' => $allApplications->where('status', ApplicationStatus::NOT_ELIGIBLE)->count(),
            'last_evaluation' => $allApplications->max('eligibility_checked_at'),
        ];

        return view('evaluation::automatic.show', compact('jobPosting', 'stats'));
    }

    /**
     * Execute automatic evaluation for a job posting (async with batch).
     */
    public function execute(Request $request, string $id)
    {
        $jobPosting = JobPosting::findOrFail($id);

        // Verificar autorización
        Gate::authorize('executeForJobPosting', [AutomaticEvaluationPolicy::class, $jobPosting]);

        $request->validate([
            'force' => 'nullable|boolean',
        ]);

        $force = $request->boolean('force');

        // Si force = true, verificar permiso de reejecutar
        if ($force) {
            Gate::authorize('reexecute', AutomaticEvaluationPolicy::class);
        }

        try {
            // Obtener aplicaciones pendientes
            $query = Application::where('status', ApplicationStatus::SUBMITTED)
                ->whereHas('jobProfile.jobPosting', fn($q) => $q->where('id', $id));

            if (!$force) {
                $query->whereNull('eligibility_checked_at');
            } else {
                // Si force, incluir también las ya evaluadas
                $query = Application::whereIn('status', [
                    ApplicationStatus::SUBMITTED,
                    ApplicationStatus::ELIGIBLE,
                    ApplicationStatus::NOT_ELIGIBLE,
                ])
                ->whereHas('jobProfile.jobPosting', fn($q) => $q->where('id', $id));
            }

            $applicationIds = $query->pluck('id')->toArray();
            $totalCount = count($applicationIds);

            if ($totalCount === 0) {
                return back()->with('warning', 'No hay postulaciones pendientes de evaluar.');
            }

            $evaluatorId = auth()->user()->id;

            // Inicializar estadísticas en cache
            $cacheKey = "evaluation_progress:{$id}";
            Cache::put($cacheKey, [
                'total' => $totalCount,
                'eligible' => 0,
                'not_eligible' => 0,
                'errors' => 0,
                'processed' => 0,
                'started_at' => now()->toIso8601String(),
            ], now()->addHours(1));

            // Dividir en batches de 25 aplicaciones cada uno
            $batchSize = 25;
            $chunks = array_chunk($applicationIds, $batchSize);

            $jobs = [];
            foreach ($chunks as $chunk) {
                $jobs[] = new EvaluateApplicationBatch($chunk, $evaluatorId, $id);
            }

            // Crear batch con callback de finalización
            $batch = Bus::batch($jobs)
                ->name("Evaluación Automática - {$jobPosting->code}")
                ->allowFailures()
                ->finally(function (Batch $batch) use ($id, $cacheKey) {
                    // Agregar información de finalización
                    $stats = Cache::get($cacheKey, []);
                    $stats['finished_at'] = now()->toIso8601String();
                    $stats['batch_finished'] = true;
                    Cache::put($cacheKey, $stats, now()->addHours(1));

                    Log::info('Batch de evaluación completado', [
                        'job_posting_id' => $id,
                        'batch_id' => $batch->id,
                        'total_jobs' => $batch->totalJobs,
                        'failed_jobs' => $batch->failedJobs,
                    ]);
                })
                ->dispatch();

            // Guardar batch_id en cache para seguimiento
            $stats = Cache::get($cacheKey);
            $stats['batch_id'] = $batch->id;
            Cache::put($cacheKey, $stats, now()->addHours(1));

            // Responder con redirección a página de progreso o mensaje
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Se inició la evaluación de {$totalCount} postulaciones.",
                    'batch_id' => $batch->id,
                    'total' => $totalCount,
                ]);
            }

            return redirect()
                ->route('evaluation.automatic.progress', $id)
                ->with('info', "Se inició la evaluación de {$totalCount} postulaciones en segundo plano.");

        } catch (\Exception $e) {
            Log::error('Error al iniciar evaluación en batch', [
                'job_posting_id' => $id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al ejecutar evaluación: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al ejecutar evaluación: ' . $e->getMessage());
        }
    }

    /**
     * Show progress page for an evaluation batch.
     */
    public function progress(string $id)
    {
        Gate::authorize('viewAny', AutomaticEvaluationPolicy::class);

        $jobPosting = JobPosting::findOrFail($id);
        $cacheKey = "evaluation_progress:{$id}";
        $stats = Cache::get($cacheKey);

        if (!$stats) {
            return redirect()
                ->route('evaluation.automatic.show', $id)
                ->with('warning', 'No hay evaluación en progreso para esta convocatoria.');
        }

        return view('evaluation::automatic.progress', compact('jobPosting', 'stats'));
    }

    /**
     * Get current progress status (AJAX endpoint).
     */
    public function getProgress(string $id)
    {
        Gate::authorize('viewAny', AutomaticEvaluationPolicy::class);

        $cacheKey = "evaluation_progress:{$id}";
        $stats = Cache::get($cacheKey);

        if (!$stats) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No hay evaluación en progreso.',
            ], 404);
        }

        // Calcular porcentaje
        $total = $stats['total'] ?? 1;
        $processed = $stats['processed'] ?? 0;
        $percentage = $total > 0 ? round(($processed / $total) * 100, 1) : 0;

        // Verificar si terminó
        $isFinished = ($stats['batch_finished'] ?? false) || $processed >= $total;

        return response()->json([
            'status' => $isFinished ? 'completed' : 'processing',
            'total' => $total,
            'processed' => $processed,
            'percentage' => $percentage,
            'eligible' => $stats['eligible'] ?? 0,
            'not_eligible' => $stats['not_eligible'] ?? 0,
            'errors' => $stats['errors'] ?? 0,
            'started_at' => $stats['started_at'] ?? null,
            'finished_at' => $stats['finished_at'] ?? null,
        ]);
    }

    /**
     * Show evaluation results for a specific application.
     * Muestra los resultados de la evaluación automática (Fase 4) desde el módulo Evaluation.
     */
    public function viewApplicationEvaluation(string $applicationId)
    {
        Gate::authorize('viewAny', AutomaticEvaluationPolicy::class);

        $application = Application::with([
            'evaluations' => function ($query) {
                $query->whereHas('phase', fn($q) => $q->where('code', 'PHASE_04_ELIGIBLE_PUB'))
                      ->with(['details.criterion', 'evaluator', 'phase']);
            },
            'jobProfile.jobPosting',
            'eligibilityChecker',
        ])->findOrFail($applicationId);

        // Obtener la evaluación automática de la Fase 4 del módulo Evaluation
        // Como ya filtramos por fase en el with(), tomamos la primera
        $evaluation = $application->evaluations->first();

        return view('evaluation::automatic.application-details', compact('application', 'evaluation'));
    }
}
