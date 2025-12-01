<?php

namespace Modules\JobPosting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\JobPosting\Entities\{JobPosting, ProcessPhase};
use Modules\JobPosting\Services\{JobPostingService, ScheduleService};
use Modules\JobPosting\Http\Requests\{
    StoreJobPostingRequest,
    UpdateJobPostingRequest,
    CancelJobPostingRequest
};
use Modules\JobPosting\Enums\JobPostingStatusEnum;
use Modules\Organization\Entities\OrganizationalUnit;

class JobPostingController extends Controller
{
    public function __construct(
        protected JobPostingService $jobPostingService,
        protected ScheduleService $scheduleService
    ) {}

    /**
     * Listado de convocatorias
     */
    public function index(Request $request): View
    {
        return $this->dashboard($request);
    }

    public function listAll(Request $request): View
    {
        $filters = $request->only(['year', 'status', 'search', 'sort_by', 'sort_order']);

        $jobPostings = $this->jobPostingService->list($filters, 15);
        $statistics = $this->jobPostingService->getStatistics($request->get('year'));
        $availableYears = $this->jobPostingService->getAvailableYears();

        return view('jobposting::index', compact(
            'jobPostings',
            'statistics',
            'availableYears',
            'filters'
        ));
    }

    /**
     * Formulario de creación
     */
    public function create(): View
    {
        $phases = ProcessPhase::active()->ordered()->get();
        $organizationalUnits = OrganizationalUnit::active()->orderBy('name')->get();
        $statuses = JobPostingStatusEnum::cases();

        return view('jobposting::create', compact(
            'phases',
            'organizationalUnits',
            'statuses'
        ));
    }

    /**
     * Guardar convocatoria
     */
    public function store(StoreJobPostingRequest $request): RedirectResponse
    {
        try {
            // Delegamos toda la lógica al servicio
            $jobPosting = $this->jobPostingService->create(
                $request->validated(),
                auth()->user()
            );

            return redirect()
                ->route('jobposting.show', $jobPosting)
                ->with('success', '✅ Convocatoria creada exitosamente con código: ' . $jobPosting->code);

        } catch (\Exception $e) {
            \Log::error('Error creando convocatoria: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', '❌ Error al crear convocatoria: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de convocatoria
     */
    public function show(JobPosting $jobPosting): View
    {
        // Verificar permiso de visualización
        if (!auth()->user()->hasPermission('jobposting.view.posting')) {
            abort(403, 'No tiene permisos para ver esta convocatoria.');
        }

        $jobPosting->load([
            'publisher',
            'schedules.phase',
            'schedules.responsibleUnit',
            'history.user',
            'jobProfiles'
        ]);

        // Ordenar los horarios por el número de fase
        $sortedSchedules = $jobPosting->schedules->sortBy('phase.phase_number');
        $jobPosting->setRelation('schedules', $sortedSchedules);

        // Próxima fase: la primera que NO esté completada
        $nextPhase = $sortedSchedules->first(function ($schedule) {
            return $schedule->status !== \Modules\JobPosting\Enums\ScheduleStatusEnum::COMPLETED
                && $schedule->status !== 'COMPLETED';
        });

        // Fase actual (la que está en progreso)
        $currentPhase = $sortedSchedules->firstWhere('status', 'IN_PROGRESS');

        if (!$currentPhase) {
            $currentPhase = $nextPhase;
        }

        // Estadísticas
        $progress = $this->scheduleService->getScheduleProgress($jobPosting);
        $delayedPhases = $this->scheduleService->getDelayedPhases($jobPosting);

        return view('jobposting::show', compact(
            'jobPosting',
            'currentPhase',
            'nextPhase',
            'progress',
            'delayedPhases'
        ));
    }

    /**
     * Formulario de edición
     */
    public function edit(JobPosting $jobPosting): View
    {
        // Verificar permiso de actualización
        if (!auth()->user()->hasPermission('jobposting.update.posting')) {
            abort(403, 'No tiene permisos para editar convocatorias.');
        }

        if (!$jobPosting->canBeEdited()) {
            abort(403, 'Esta convocatoria no puede ser editada en su estado actual.');
        }

        $phases = ProcessPhase::active()->ordered()->get();
        $organizationalUnits = OrganizationalUnit::active()->orderBy('name')->get();
        $statuses = JobPostingStatusEnum::cases();

        return view('jobposting::edit', compact(
            'jobPosting',
            'phases',
            'organizationalUnits',
            'statuses'
        ));
    }

    /**
     * Actualizar convocatoria
     */
    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): RedirectResponse
    {
        // Verificar permiso de actualización
        if (!auth()->user()->hasPermission('jobposting.update.posting')) {
            abort(403, 'No tiene permisos para actualizar convocatorias.');
        }

        if (!$jobPosting->canBeEdited()) {
            return back()->with('error', '❌ Esta convocatoria no puede ser editada en su estado actual.');
        }

        try {
            $this->jobPostingService->update(
                $jobPosting,
                $request->validated(),
                auth()->user()
            );

            return redirect()
                ->route('jobposting.show', $jobPosting)
                ->with('success', '✅ Convocatoria actualizada exitosamente.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', '❌ Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar convocatoria (Soft Delete)
     */
    public function destroy(JobPosting $jobPosting): RedirectResponse
    {
        // Verificar permiso de eliminación
        if (!auth()->user()->hasPermission('jobposting.delete.posting')) {
            abort(403, 'No tiene permisos para eliminar convocatorias.');
        }

        try {
            $this->jobPostingService->delete($jobPosting, auth()->user());

            return redirect()
                ->route('jobposting.index')
                ->with('success', '✅ Convocatoria eliminada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Publicar convocatoria
     */
    public function publish(JobPosting $jobPosting): RedirectResponse
    {
        // Verificar permiso de publicación
        if (!auth()->user()->hasPermission('jobposting.publish.posting')) {
            abort(403, 'No tiene permisos para publicar convocatorias.');
        }

        try {
            $this->jobPostingService->publish($jobPosting, auth()->user());

            return back()->with('success', '📢 Convocatoria publicada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Iniciar proceso
     */
    public function startProcess(JobPosting $jobPosting): RedirectResponse
    {
        try {
            $this->jobPostingService->startProcess($jobPosting, auth()->user());

            return back()->with('success', '▶️ Proceso de convocatoria iniciado.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Finalizar convocatoria
     */
    public function finalize(JobPosting $jobPosting): RedirectResponse
    {
        try {
            $this->jobPostingService->finalize($jobPosting, auth()->user());

            return back()->with('success', '✅ Convocatoria finalizada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Cancelar convocatoria
     */
    public function cancel(CancelJobPostingRequest $request, JobPosting $jobPosting): RedirectResponse
    {
        try {
            $this->jobPostingService->cancel(
                $jobPosting,
                $request->input('cancellation_reason'),
                auth()->user()
            );

            return redirect()
                ->route('jobposting.show', $jobPosting)
                ->with('success', '❌ Convocatoria cancelada.');
        } catch (\Exception $e) {
            return back()->with('error', '❌ ' . $e->getMessage());
        }
    }

    /**
     * Clonar convocatoria
     */
    public function clone(JobPosting $jobPosting): RedirectResponse
    {
        try {
            $newJobPosting = $this->jobPostingService->clone($jobPosting, auth()->user());

            return redirect()
                ->route('jobposting.edit', $newJobPosting)
                ->with('success', '📋 Convocatoria clonada exitosamente. Nuevo código: ' . $newJobPosting->code);
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error al clonar: ' . $e->getMessage());
        }
    }

    /**
     * Ver/Gestionar cronograma
     */
    public function schedule(JobPosting $jobPosting): View
    {
        $timeline = $this->scheduleService->getTimeline($jobPosting);
        $progress = $this->scheduleService->getScheduleProgress($jobPosting);
        $currentPhase = $this->scheduleService->getCurrentPhase($jobPosting);
        $availablePhases = ProcessPhase::active()->ordered()->get();
        $organizationalUnits = OrganizationalUnit::active()->orderBy('name')->get();

        return view('jobposting::schedule', compact(
            'jobPosting',
            'timeline',
            'progress',
            'currentPhase',
            'availablePhases',
            'organizationalUnits'
        ));
    }

    /**
     * Ver historial de cambios
     */
    public function history(JobPosting $jobPosting): View
    {
        $history = $jobPosting->history()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('jobposting::history', compact('jobPosting', 'history'));
    }

    /**
     * Dashboard general
     */
    public function dashboard(Request $request)
    {
        $currentYear = $request->input('year', date('Y'));

        // Estadísticas
        $statistics = [
            'total' => JobPosting::whereYear('created_at', $currentYear)->count(),
            'activas' => JobPosting::whereYear('created_at', $currentYear)
                ->whereIn('status', ['PUBLICADA', 'EN_PROCESO'])
                ->count(),
            'por_estado' => [
                'borradores' => JobPosting::whereYear('created_at', $currentYear)
                    ->where('status', 'BORRADOR')->count(),
                'publicadas' => JobPosting::whereYear('created_at', $currentYear)
                    ->where('status', 'PUBLICADA')->count(),
                'en_proceso' => JobPosting::whereYear('created_at', $currentYear)
                    ->where('status', 'EN_PROCESO')->count(),
                'finalizadas' => JobPosting::whereYear('created_at', $currentYear)
                    ->where('status', 'FINALIZADA')->count(),
                'canceladas' => JobPosting::whereYear('created_at', $currentYear)
                    ->where('status', 'CANCELADA')->count(),
            ],
            'por_mes' => $this->getMonthlyStats($currentYear),
        ];

        // Años disponibles
        $availableYears = JobPosting::selectRaw('DISTINCT YEAR(created_at) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        // Convocatorias próximas a vencer
        $nearingEnd = JobPosting::where('status', 'PUBLICADA')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(7))
            ->orderBy('end_date', 'asc')
            ->get();

        // Convocatorias con fases retrasadas
        $delayed = JobPosting::whereHas('schedules', function($query) {
            $query->where('end_date', '<', now())
                  ->where('status', '!=', 'COMPLETADA');
        })->get();

        return view('jobposting::dashboard', compact(
            'statistics',
            'currentYear',
            'availableYears',
            'nearingEnd',
            'delayed'
        ));
    }

    protected function getMonthlyStats($year)
    {
        $monthlyData = JobPosting::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        // Llenar todos los meses (1-12) con 0 si no hay datos
        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[$i] = $monthlyData->get($i, 0);
        }

        return $result;
    }

    /* ========================================================================== */
    /*                          🔍 API DE BÚSQUEDA DINÁMICA                      */
    /* ========================================================================== */

    /**
     * API: Buscar Unidades Organizacionales
     * Endpoint: GET /api/search/organizational-units
     */
    public function searchOrganizationalUnits(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 20);

        $units = OrganizationalUnit::query()
            ->where('is_active', true)
            ->when($query, function($q) use ($query) {
                $q->where(function($q2) use ($query) {
                    $q2->where('name', 'like', "%{$query}%")
                       ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get(['id', 'name', 'code', 'type']);

        return response()->json([
            'success' => true,
            'data' => $units->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'text' => "{$unit->code} - {$unit->name}",
                    'name' => $unit->name,
                    'code' => $unit->code,
                    'type' => $unit->type,
                ];
            }),
            'total' => $units->count()
        ]);
    }

    /**
     * API: Buscar Fases del Proceso
     * Endpoint: GET /api/search/process-phases
     */
    public function searchProcessPhases(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 20);
        $excludeIds = $request->input('exclude', []); // IDs a excluir

        $phases = ProcessPhase::query()
            ->where('is_active', true)
            ->when($query, function($q) use ($query) {
                $q->where(function($q2) use ($query) {
                    $q2->where('name', 'like', "%{$query}%")
                       ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->when(!empty($excludeIds), function($q) use ($excludeIds) {
                $q->whereNotIn('id', $excludeIds);
            })
            ->orderBy('phase_number', 'asc')
            ->limit($limit)
            ->get(['id', 'name', 'code', 'phase_number', 'requires_evaluation']);

        return response()->json([
            'success' => true,
            'data' => $phases->map(function($phase) {
                return [
                    'id' => $phase->id,
                    'text' => "Fase {$phase->phase_number}: {$phase->name}",
                    'name' => $phase->name,
                    'code' => $phase->code,
                    'phase_number' => $phase->phase_number,
                    'requires_evaluation' => $phase->requires_evaluation,
                ];
            }),
            'total' => $phases->count()
        ]);
    }

    /**
     * API: Buscar Convocatorias
     * Endpoint: GET /api/search/job-postings
     */
    public function searchJobPostings(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $year = $request->input('year', null);
        $status = $request->input('status', null);
        $limit = $request->input('limit', 20);

        $jobPostings = JobPosting::query()
            ->when($query, function($q) use ($query) {
                $q->where(function($q2) use ($query) {
                    $q2->where('title', 'like', "%{$query}%")
                       ->orWhere('code', 'like', "%{$query}%");
                });
            })
            ->when($year, function($q) use ($year) {
                $q->where('year', $year);
            })
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'code', 'title', 'year', 'status']);

        return response()->json([
            'success' => true,
            'data' => $jobPostings->map(function($posting) {
                return [
                    'id' => $posting->id,
                    'text' => "{$posting->code} - {$posting->title}",
                    'code' => $posting->code,
                    'title' => $posting->title,
                    'year' => $posting->year,
                    'status' => $posting->status->label(),
                ];
            }),
            'total' => $jobPostings->count()
        ]);
    }

    /**
     * API: Validar código único
     * Endpoint: GET /api/validate/job-posting-code
     */
    public function validateCode(Request $request): JsonResponse
    {
        $code = $request->input('code');
        $excludeId = $request->input('exclude_id', null);

        $exists = JobPosting::query()
            ->where('code', $code)
            ->when($excludeId, function($q) use ($excludeId) {
                $q->where('id', '!=', $excludeId);
            })
            ->exists();

        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'El código ya existe' : 'Código disponible',
        ]);
    }

    /**
     * API: Obtener siguiente código disponible
     * Endpoint: GET /api/generate/job-posting-code
     */
    public function generateCode(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);

        // Reutilizar la lógica del servicio
        $lastPosting = JobPosting::withTrashed()
            ->where('year', $year)
            ->where('code', 'LIKE', "CONV-{$year}-%")
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastPosting) {
            $nextCode = "CONV-{$year}-001";
        } else {
            $parts = explode('-', $lastPosting->code);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
            $nextCode = "CONV-{$year}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'success' => true,
            'code' => $nextCode,
            'year' => $year,
        ]);
    }

    /**
     * API: Vista previa de cronograma antes de crear
     * Endpoint: POST /api/preview/schedule
     */
    public function previewSchedule(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now());

        $phases = ProcessPhase::where('is_active', true)
            ->orderBy('phase_number', 'asc')
            ->get();

        $currentDate = \Carbon\Carbon::parse($startDate);
        $preview = [];

        foreach ($phases as $phase) {
            $daysDuration = $phase->default_duration_days ?? match($phase->phase_number) {
                3 => 2,
                6 => 3,
                8 => 2,
                default => 1
            };

            $endDate = (clone $currentDate)->addDays($daysDuration - 1);

            $preview[] = [
                'phase_number' => $phase->phase_number,
                'phase_name' => $phase->name,
                'start_date' => $currentDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration_days' => $daysDuration,
            ];

            $currentDate = (clone $endDate)->addDay();
        }

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'total_phases' => count($preview),
            'start_date' => $startDate,
            'end_date' => $currentDate->subDay()->format('Y-m-d'),
        ]);
    }

    /**
     * API: Verificar duplicados en cronograma
     * Endpoint: GET /api/check/duplicate-phase
     */
    public function checkDuplicatePhase(Request $request): JsonResponse
    {
        $jobPostingId = $request->input('job_posting_id');
        $phaseId = $request->input('phase_id');

        $exists = \Modules\JobPosting\Entities\JobPostingSchedule::where('job_posting_id', $jobPostingId)
            ->where('process_phase_id', $phaseId)
            ->exists();

        return response()->json([
            'duplicate' => $exists,
            'message' => $exists ? 'Esta fase ya está en el cronograma' : 'Fase disponible',
        ]);
    }

    /**
     * API: Regenerar cronograma completo
     * Endpoint: POST /api/regenerate/schedule/{jobPosting}
     */
    public function regenerateSchedule(Request $request, JobPosting $jobPosting): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now());

            $updated = $this->jobPostingService->regenerateSchedule($jobPosting, $startDate);

            return response()->json([
                'success' => true,
                'message' => 'Cronograma regenerado exitosamente',
                'total_phases' => $updated->schedules->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al regenerar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
