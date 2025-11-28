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
     * Listado de convocatorias
     */
    public function index(Request $request): View
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
     * Guardar convocatoria (CORREGIDO)
     */
    public function store(StoreJobPostingRequest $request): RedirectResponse
    {
        try {
            // Delegamos toda la lógica al servicio.
            // El servicio se encargará de:
            // 1. Generar el código único (respetando eliminados)
            // 2. Crear el registro
            // 3. Generar el cronograma automático si auto_schedule es true
            $jobPosting = $this->jobPostingService->create(
                $request->validated(),
                auth()->user()
            );

            return redirect()
                ->route('jobposting.show', $jobPosting)
                ->with('success', '✅ Convocatoria creada exitosamente con código: ' . $jobPosting->code);

        } catch (\Exception $e) {
            // Loguear el error para depuración interna
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
        $jobPosting->load([
            'publisher',
            'schedules.phase', // Cargamos la relación con la fase
            'schedules.responsibleUnit',
            'history.user'
        ]);

        // 1. CORRECCIÓN IMPORTANTE: Ordenar los horarios por el número de fase (1 al 12)
        // Esto arregla la lista desordenada en la vista
        $sortedSchedules = $jobPosting->schedules->sortBy('phase.phase_number');
        $jobPosting->setRelation('schedules', $sortedSchedules);

        // 2. CORRECCIÓN PRÓXIMA FASE: 
        // Buscamos la primera fase que NO esté completada, basándonos en el orden lógico (1, 2, 3...)
        // y no solo en la fecha.
        $nextPhase = $sortedSchedules->first(function ($schedule) {
            return $schedule->status !== \Modules\JobPosting\Enums\ScheduleStatusEnum::COMPLETED 
                && $schedule->status !== 'COMPLETED'; // Por si acaso es string
        });

        // Fase actual (la que está en progreso)
        $currentPhase = $sortedSchedules->firstWhere('status', 'IN_PROGRESS');

        // Si no hay ninguna en progreso, la "actual" visualmente puede ser la "siguiente"
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
            // El servicio genera el nuevo código único automáticamente
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
    public function dashboard(): View
    {
        $currentYear = now()->year;
        $statistics = $this->jobPostingService->getStatistics($currentYear);
        $nearingEnd = $this->jobPostingService->getNearingEnd(7);
        $delayed = $this->jobPostingService->getDelayed();
        $availableYears = $this->jobPostingService->getAvailableYears();

        return view('jobposting::dashboard', compact(
            'statistics',
            'nearingEnd',
            'delayed',
            'availableYears',
            'currentYear'
        ));
    }
}