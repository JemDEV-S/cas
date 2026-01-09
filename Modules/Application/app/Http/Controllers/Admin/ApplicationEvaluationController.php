<?php

namespace Modules\Application\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Application\Services\AutoGraderService;
use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\JobPosting\Entities\JobPosting;
use Modules\Application\Jobs\EvaluateApplicationBatch;
use Modules\Application\Events\BatchEvaluationCompleted;

class ApplicationEvaluationController extends Controller
{
    public function __construct(
        private AutoGraderService $autoGrader
    ) {}

    /**
     * Mostrar dashboard de evaluación
     */
    public function index(string $postingId)
    {
        $this->authorize('viewAny', Application::class);

        $posting = JobPosting::with(['schedules.phase'])->findOrFail($postingId);

        $applications = Application::whereHas('vacancy.jobProfileRequest.jobPosting', fn($q) =>
                $q->where('id', $postingId)
            )
            ->with(['vacancy.jobProfileRequest', 'latestEvaluation'])
            ->get();

        $stats = [
            'total' => $applications->count(),
            'draft' => $applications->where('status', ApplicationStatus::DRAFT)->count(),
            'submitted' => $applications->where('status', ApplicationStatus::SUBMITTED)->count(),
            'eligible' => $applications->where('status', ApplicationStatus::ELIGIBLE)->count(),
            'not_eligible' => $applications->where('status', ApplicationStatus::NOT_ELIGIBLE)->count(),
            'evaluated' => $applications->whereNotNull('eligibility_checked_at')->count(),
            'pending' => $applications->where('status', ApplicationStatus::SUBMITTED)
                ->whereNull('eligibility_checked_at')->count(),
        ];

        return view('application::admin.evaluation.index', compact('posting', 'applications', 'stats'));
    }

    /**
     * Ejecutar evaluación automática
     */
    public function evaluate(Request $request, string $postingId)
    {
        $this->authorize('create', Application::class);

        $posting = JobPosting::findOrFail($postingId);

        // Validar fase
        $currentPhase = $posting->getCurrentPhase();
        if (!$currentPhase || $currentPhase->phase->code !== 'PHASE_03_REGISTRATION') {
            return redirect()
                ->back()
                ->with('error', 'La evaluación solo puede ejecutarse en la Fase 3 (Registro de Postulantes)');
        }

        // Obtener postulaciones pendientes
        $applicationIds = Application::where('status', ApplicationStatus::SUBMITTED)
            ->whereHas('vacancy.jobProfileRequest.jobPosting', fn($q) => $q->where('id', $postingId))
            ->pluck('id');

        if ($applicationIds->isEmpty()) {
            return redirect()
                ->back()
                ->with('warning', 'No hay postulaciones pendientes de evaluar');
        }

        // Decidir si procesar en background o sincrónicamente
        $useQueue = $applicationIds->count() > 10;

        if ($useQueue) {
            // Dividir en lotes y despachar jobs
            $batches = $applicationIds->chunk(50);

            foreach ($batches as $batch) {
                EvaluateApplicationBatch::dispatch($batch->toArray(), auth()->id());
            }

            return redirect()
                ->back()
                ->with('success', "Se ha iniciado la evaluación de {$applicationIds->count()} postulaciones. El proceso se está ejecutando en segundo plano y recibirá una notificación cuando finalice.");
        } else {
            // Procesar sincrónicamente para pocas postulaciones
            $stats = $this->evaluateSynchronously($applicationIds, auth()->id());

            return redirect()
                ->back()
                ->with('success', "Evaluación completada: {$stats['eligible']} APTOS, {$stats['not_eligible']} NO APTOS");
        }
    }

    /**
     * Evaluar postulaciones sincrónicamente
     */
    private function evaluateSynchronously($applicationIds, $userId): array
    {
        $stats = [
            'total' => $applicationIds->count(),
            'eligible' => 0,
            'not_eligible' => 0,
            'errors' => 0,
        ];

        $applications = Application::whereIn('id', $applicationIds)
            ->with([
                'academics.career',
                'experiences',
                'trainings',
                'professionalRegistrations',
                'knowledge',
                'vacancy.jobProfileRequest.careers'
            ])
            ->get();

        foreach ($applications as $application) {
            try {
                $result = $this->autoGrader->evaluateEligibility($application);
                $this->autoGrader->applyAutoGrading($application, $userId);

                if ($result['is_eligible']) {
                    $stats['eligible']++;
                } else {
                    $stats['not_eligible']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                \Log::error('Error evaluando postulación', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /**
     * Publicar resultados de evaluación
     */
    public function publish(Request $request, string $postingId)
    {
        $this->authorize('update', Application::class);

        $posting = JobPosting::findOrFail($postingId);

        if ($posting->results_published ?? false) {
            return redirect()
                ->back()
                ->with('error', 'Los resultados ya fueron publicados anteriormente');
        }

        // Verificar que todas estén evaluadas
        $pending = Application::where('status', ApplicationStatus::SUBMITTED)
            ->whereHas('vacancy.jobProfileRequest.jobPosting', fn($q) => $q->where('id', $postingId))
            ->whereNull('eligibility_checked_at')
            ->count();

        if ($pending > 0) {
            return redirect()
                ->back()
                ->with('error', "Aún hay {$pending} postulaciones sin evaluar. Complete la evaluación antes de publicar los resultados.");
        }

        // Publicar resultados
        DB::transaction(function () use ($posting) {
            $posting->update([
                'results_published' => true,
                'results_published_at' => now(),
                'results_published_by' => auth()->id()
            ]);

            // Disparar evento
            $stats = $this->getEvaluationStats($posting->id);
            event(new BatchEvaluationCompleted($posting, $stats));
        });

        return redirect()
            ->back()
            ->with('success', 'Resultados publicados correctamente. Los postulantes ahora pueden ver sus resultados de elegibilidad.');
    }

    /**
     * Override manual de resultado de evaluación
     */
    public function override(Request $request, string $applicationId)
    {
        $this->authorize('update', Application::class);

        $validated = $request->validate([
            'is_eligible' => 'required|boolean',
            'reason' => 'required_if:is_eligible,false|string|max:1000'
        ]);

        $application = Application::findOrFail($applicationId);

        DB::transaction(function () use ($application, $validated) {
            $application->update([
                'is_eligible' => $validated['is_eligible'],
                'status' => $validated['is_eligible']
                    ? ApplicationStatus::ELIGIBLE
                    : ApplicationStatus::NOT_ELIGIBLE,
                'ineligibility_reason' => $validated['reason'] ?? null,
                'eligibility_checked_at' => now(),
                'eligibility_checked_by' => auth()->id(),
            ]);

            // Guardar en historial
            $application->history()->create([
                'action' => 'eligibility_overridden',
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'details' => [
                    'result' => $validated['is_eligible'] ? 'APTO' : 'NO_APTO',
                    'manual_reason' => $validated['reason'] ?? null,
                    'overridden_by' => auth()->user()->name,
                ]
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Resultado de elegibilidad modificado correctamente');
    }

    /**
     * Ver detalle de evaluación de una postulación
     */
    public function show(string $applicationId)
    {
        $this->authorize('view', Application::class);

        $application = Application::with([
            'latestEvaluation',
            'academics.career',
            'experiences',
            'trainings',
            'professionalRegistrations',
            'knowledge',
            'vacancy.jobProfileRequest.careers'
        ])->findOrFail($applicationId);

        $evaluation = $application->latestEvaluation;

        return view('application::admin.evaluation.show', compact('application', 'evaluation'));
    }

    /**
     * Obtener estadísticas de evaluación
     */
    private function getEvaluationStats(string $postingId): array
    {
        $applications = Application::whereHas('vacancy.jobProfileRequest.jobPosting', fn($q) =>
            $q->where('id', $postingId)
        )->get();

        return [
            'total' => $applications->count(),
            'eligible' => $applications->where('is_eligible', true)->count(),
            'not_eligible' => $applications->where('is_eligible', false)->count(),
            'pending' => $applications->whereNull('eligibility_checked_at')->count(),
        ];
    }
}
