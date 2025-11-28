<?php

namespace Modules\JobPosting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
     * Display a listing of the resource.
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
     * Show the form for creating a new resource.
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
     * Store a newly created resource in storage.
     */
    public function store(StoreJobPostingRequest $request): RedirectResponse
    {
        try {
            $jobPosting = $this->jobPostingService->create(
                $request->validated(),
                auth()->user()
            );

            // Si se solicita crear cronograma automático
            if ($request->boolean('create_schedule')) {
                $startDate = $request->date('schedule_start_date') ?? now()->addDays(7);
                $this->scheduleService->createFullSchedule($jobPosting, $startDate);
            }

            return redirect()
                ->route('jobposting.show', $jobPosting)
                ->with('success', '✅ Convocatoria creada exitosamente con código: ' . $jobPosting->code);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', '❌ Error al crear convocatoria: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JobPosting $jobPosting): View
    {
        $jobPosting->load([
            'publisher',
            'schedules.phase',
            'schedules.responsibleUnit',
            'history.user'
        ]);

        $currentPhase = $this->scheduleService->getCurrentPhase($jobPosting);
        $nextPhase = $this->scheduleService->getNextPhase($jobPosting);
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
     * Show the form for editing the specified resource.
     */
    public function edit(JobPosting $jobPosting): View
    {
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
     * Update the specified resource in storage.
     */
    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting): RedirectResponse
    {
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
     * Remove the specified resource from storage.
     */
    public function destroy(JobPosting $jobPosting): RedirectResponse
    {
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
                ->with('success', '📋 Convocatoria clonada exitosamente. Código: ' . $newJobPosting->code);
        } catch (\Exception $e) {
            return back()->with('error', '❌ Error al clonar: ' . $e->getMessage());
        }
    }

    /**
     * Ver cronograma
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
     * Ver historial
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
     * Dashboard de métricas
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
}
