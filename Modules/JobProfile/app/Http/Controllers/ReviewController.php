<?php

namespace Modules\JobProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Modules\JobProfile\Services\ReviewService;
use Modules\JobProfile\Services\JobProfileService;
use Modules\Core\Exceptions\BusinessRuleException;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService,
        protected JobProfileService $jobProfileService
    ) {}

    /**
     * Muestra la lista de perfiles pendientes de revisión
     */
    public function index(Request $request): View
    {
        // Obtener filtros desde la request
        $filters = [
            'search' => $request->input('search'),
            'organizational_unit_id' => $request->input('organizational_unit_id'),
            'position_code_id' => $request->input('position_code_id'),
            'requested_by' => $request->input('requested_by'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        // Obtener perfiles con filtros aplicados
        $jobProfiles = $this->jobProfileService->getByStatusWithFilters('in_review', $filters);

        // Obtener datos para los selectores de filtros
        $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $positionCodes = \Modules\JobProfile\Entities\PositionCode::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $requesters = \Modules\User\Entities\User::whereHas('requestedJobProfiles')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return view('jobprofile::review.index', compact(
            'jobProfiles',
            'organizationalUnits',
            'positionCodes',
            'requesters',
            'filters'
        ));
    }

    /**
     * Muestra el formulario de revisión de un perfil
     */
    public function show(string $id): View
    {
        $jobProfile = $this->jobProfileService->findById($id);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        if (!$jobProfile->isInReview()) {
            abort(403, 'Este perfil no está en revisión.');
        }

        return view('jobprofile::review.show', compact('jobProfile'));
    }

    /**
     * Envía un perfil a revisión
     */
    public function submit(string $id): RedirectResponse
    {
        try {
            $userId = auth()->id();
            $this->reviewService->submitForReview($id, $userId);

            return redirect()
                ->route('jobprofile.show', $id)
                ->with('success', 'Perfil enviado a revisión exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar a revisión: ' . $e->getMessage());
        }
    }

    /**
     * Solicita modificaciones a un perfil
     */
    public function requestModification(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'comments' => 'required|string|min:10',
        ], [
            'comments.required' => 'Los comentarios son obligatorios.',
            'comments.min' => 'Los comentarios deben tener al menos 10 caracteres.',
        ]);

        try {
            $reviewerId = auth()->id();
            $this->reviewService->requestModification($id, $reviewerId, $request->comments);

            return redirect()
                ->route('jobprofile.review.index')
                ->with('success', 'Modificación solicitada exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al solicitar modificación: ' . $e->getMessage());
        }
    }

    /**
     * Aprueba un perfil
     */
    public function approve(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'comments' => 'nullable|string|max:500',
        ]);

        try {
            $approverId = auth()->id();
            $this->reviewService->approve($id, $approverId, $request->comments);

            return redirect()
                ->route('jobprofile.review.index')
                ->with('success', 'Perfil aprobado exitosamente. Las vacantes se han generado automáticamente.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al aprobar el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Rechaza un perfil
     */
    public function reject(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10',
        ], [
            'reason.required' => 'La razón del rechazo es obligatoria.',
            'reason.min' => 'La razón debe tener al menos 10 caracteres.',
        ]);

        try {
            $reviewerId = auth()->id();
            $this->reviewService->reject($id, $reviewerId, $request->reason);

            return redirect()
                ->route('jobprofile.review.index')
                ->with('success', 'Perfil rechazado.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al rechazar el perfil: ' . $e->getMessage());
        }
    }
}
