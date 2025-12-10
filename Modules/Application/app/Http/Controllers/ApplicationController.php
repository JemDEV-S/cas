<?php

namespace Modules\Application\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Application\Services\ApplicationService;
use Modules\Application\Services\EligibilityCalculatorService;
use Modules\Application\Repositories\Contracts\ApplicationRepositoryInterface;
use Modules\Application\Http\Requests\StoreApplicationRequest;
use Modules\Application\Http\Requests\UpdateApplicationRequest;
use Modules\Application\DTOs\ApplicationDTO;
use Modules\Application\Entities\Application;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService,
        protected ApplicationRepositoryInterface $repository,
        protected EligibilityCalculatorService $eligibilityCalculator
    ) {}

    /**
     * Listar todas las postulaciones
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'vacancy_id', 'dni', 'name', 'is_eligible', 'date_from', 'date_to']);
        $applications = $this->repository->paginate($filters, 15);

        return view('application::index', compact('applications', 'filters'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request): View
    {
        $vacancyId = $request->query('vacancy_id');
        return view('application::create', compact('vacancyId'));
    }

    /**
     * Guardar nueva postulación
     */
    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        try {
            $dto = ApplicationDTO::fromArray($request->validated());
            $application = $this->applicationService->create($dto);

            return redirect()
                ->route('application.show', $application->id)
                ->with('success', 'Postulación creada exitosamente. Código: ' . $application->code);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear la postulación: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalle de una postulación
     */
    public function show(string $id): View
    {
        $application = $this->repository->find($id);

        if (!$application) {
            abort(404, 'Postulación no encontrada');
        }

        $experienceData = $this->applicationService->calculateExperience($application);
        $statistics = $this->applicationService->getStatistics($application);

        return view('application::show', compact('application', 'experienceData', 'statistics'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(string $id): View
    {
        $application = $this->repository->find($id);

        if (!$application) {
            abort(404, 'Postulación no encontrada');
        }

        if (!$application->isEditable()) {
            return redirect()
                ->route('application.show', $application->id)
                ->with('error', 'Esta postulación no puede ser editada en su estado actual');
        }

        return view('application::edit', compact('application'));
    }

    /**
     * Actualizar postulación
     */
    public function update(UpdateApplicationRequest $request, string $id): RedirectResponse
    {
        try {
            $application = $this->repository->find($id);

            if (!$application) {
                abort(404, 'Postulación no encontrada');
            }

            $dto = ApplicationDTO::fromArray($request->validated());
            $application = $this->applicationService->update($application, $dto);

            return redirect()
                ->route('application.show', $application->id)
                ->with('success', 'Postulación actualizada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar postulación (soft delete)
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $application = $this->repository->find($id);

            if (!$application) {
                abort(404, 'Postulación no encontrada');
            }

            $this->repository->delete($application);

            return redirect()
                ->route('application.index')
                ->with('success', 'Postulación eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Desistir de la postulación
     */
    public function withdraw(Request $request, string $id): RedirectResponse
    {
        try {
            $application = $this->repository->find($id);

            if (!$application) {
                abort(404, 'Postulación no encontrada');
            }

            $reason = $request->input('reason');
            $this->applicationService->withdraw($application, $reason);

            return redirect()
                ->route('application.show', $application->id)
                ->with('success', 'Se ha registrado el desistimiento de la postulación');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Evaluar elegibilidad
     */
    public function evaluateEligibility(string $id): RedirectResponse
    {
        try {
            $application = $this->repository->find($id);

            if (!$application) {
                abort(404, 'Postulación no encontrada');
            }

            $this->applicationService->evaluateEligibility($application, auth()->id());

            return redirect()
                ->route('application.show', $application->id)
                ->with('success', 'Elegibilidad evaluada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Ver historial de cambios
     */
    public function history(string $id): View
    {
        $application = $this->repository->find($id);

        if (!$application) {
            abort(404, 'Postulación no encontrada');
        }

        $history = $application->history()
            ->with('performer')
            ->paginate(20);

        return view('application::history', compact('application', 'history'));
    }
}
