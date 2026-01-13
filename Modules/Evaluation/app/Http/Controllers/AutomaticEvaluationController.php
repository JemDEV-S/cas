<?php

namespace Modules\Evaluation\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\Evaluation\Entities\Evaluation;
use Modules\JobPosting\Entities\ProcessPhase;
use Modules\Evaluation\Policies\AutomaticEvaluationPolicy;

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
            ->with(['jobProfiles.vacancies.applications' => function ($query) {
                $query->whereIn('status', [
                    ApplicationStatus::SUBMITTED,
                    ApplicationStatus::ELIGIBLE,
                    ApplicationStatus::NOT_ELIGIBLE
                ]);
            }])
            ->published()
            ->whereHas('jobProfiles.vacancies.applications', function ($query) {
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
                foreach ($jobProfile->vacancies as $vacancy) {
                    $stats['total_pending'] += $vacancy->applications
                        ->where('status', ApplicationStatus::SUBMITTED)
                        ->whereNull('eligibility_checked_at')
                        ->count();

                    $stats['total_evaluated'] += $vacancy->applications
                        ->whereIn('status', [ApplicationStatus::ELIGIBLE, ApplicationStatus::NOT_ELIGIBLE])
                        ->whereNotNull('eligibility_checked_at')
                        ->count();
                }
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
            'jobProfiles.vacancies.applications' => function ($query) {
                $query->whereIn('status', [
                    ApplicationStatus::SUBMITTED,
                    ApplicationStatus::ELIGIBLE,
                    ApplicationStatus::NOT_ELIGIBLE
                ])->orderBy('created_at', 'desc');
            },
            'jobProfiles.vacancies.applications.evaluations' => function ($query) {
                $query->whereHas('phase', fn($q) => $q->where('code', 'PHASE_04_ELIGIBLE_PUB'));
            }
        ])->findOrFail($id);

        // Calcular estadísticas
        $allApplications = $jobPosting->jobProfiles->flatMap(function ($jobProfile) {
            return $jobProfile->vacancies->flatMap->applications;
        });

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
     * Execute automatic evaluation for a job posting.
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
            // Contar aplicaciones pendientes
            $pendingCount = Application::whereIn('status', [
                ApplicationStatus::SUBMITTED,
                $force ? ApplicationStatus::ELIGIBLE : null,
                $force ? ApplicationStatus::NOT_ELIGIBLE : null,
            ])
            ->whereHas('vacancy.jobProfile.jobPosting', fn($q) => $q->where('id', $id))
            ->when(!$force, fn($q) => $q->whereNull('eligibility_checked_at'))
            ->count();

            if ($pendingCount === 0) {
                return back()->with('warning', 'No hay postulaciones pendientes de evaluar.');
            }

            // Ejecutar comando en modo síncrono
            $evaluatorId = auth()->user()->id;

            Artisan::call('applications:evaluate', [
                'posting_id' => $id,
                '--evaluator' => $evaluatorId,
                '--force' => $force,
            ]);

            $output = Artisan::output();

            return back()->with('success', "Se evaluaron {$pendingCount} postulaciones exitosamente.")->with('output', $output);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al ejecutar evaluación: ' . $e->getMessage());
        }
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
                      ->with(['details.criterion', 'evaluator']);
            },
            'vacancy.jobProfile',
        ])->findOrFail($applicationId);

        // Obtener la evaluación automática de la Fase 4 del módulo Evaluation
        $evaluation = $application->evaluations
            ->where('phase.code', 'PHASE_04_ELIGIBLE_PUB')
            ->first();

        return view('evaluation::automatic.application-details', compact('application', 'evaluation'));
    }
}
