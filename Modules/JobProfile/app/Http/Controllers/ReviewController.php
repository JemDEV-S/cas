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

        // Cargar datos necesarios para el formulario de edición
        $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        $positionCodes = \Modules\JobProfile\Entities\PositionCode::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn($pc) => [$pc->id => $pc->code . ' - ' . $pc->name])
            ->toArray();

        // Obtener datos completos de position codes para autocompletado
        $positionCodesData = \Modules\JobProfile\Entities\PositionCode::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->keyBy('id')
            ->map(function($pc) {
                return [
                    'education_level' => $pc->education_level_required,
                    'education_levels' => $pc->education_levels_accepted ?? [$pc->education_level_required],
                    'title_required' => $pc->requires_professional_title,
                    'colegiatura_required' => $pc->requires_professional_license,
                    'general_experience_years' => $pc->min_professional_experience,
                    'specific_experience_years' => $pc->min_specific_experience,
                    'base_salary' => $pc->base_salary,
                    'formatted_salary' => 'S/ ' . number_format($pc->base_salary, 2),
                ];
            })
            ->toArray();

        $educationOptions = \Modules\JobProfile\Enums\EducationLevelEnum::selectOptions();

        return view('jobprofile::review.show', compact(
            'jobProfile',
            'organizationalUnits',
            'positionCodes',
            'positionCodesData',
            'educationOptions'
        ));
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

    /**
     * Actualiza un perfil durante la revisión
     */
    public function update(\Modules\JobProfile\Http\Requests\UpdateJobProfileRequest $request, string $id): RedirectResponse
    {
        $jobProfile = $this->jobProfileService->findById($id);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        // Verificar autorización usando la policy
        $this->authorize('updateDuringReview', $jobProfile);

        $validatedData = $request->validated();

        try {
            // Actualizar directamente sin pasar por la validación canEdit()
            // ya que el revisor tiene permiso para editar durante la revisión
            \DB::transaction(function () use ($jobProfile, $validatedData) {
                // Excluir campos específicos de los datos validados
                $dataToUpdate = collect($validatedData)
                    ->except(['requirements', 'responsibilities'])
                    ->toArray();

                // Actualizar el perfil directamente
                $jobProfile->update($dataToUpdate);
            });

            return redirect()
                ->route('jobprofile.review.show', $id)
                ->with('success', 'Perfil actualizado exitosamente. Puede continuar revisando o aprobar el perfil.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }
}
