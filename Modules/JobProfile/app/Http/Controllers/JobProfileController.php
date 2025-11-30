<?php

namespace Modules\JobProfile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\JobProfile\Services\JobProfileService;
use Modules\Core\Exceptions\BusinessRuleException;
use Modules\JobProfile\Http\Requests\StoreJobProfileRequest;
use Modules\JobProfile\Enums\EducationLevelEnum;


class JobProfileController extends Controller
{
    public function __construct(
        protected JobProfileService $jobProfileService,
        protected \Modules\JobProfile\Services\ReviewService $reviewService
    ) {}

    /**
     * Display a listing of job profiles.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status');

        $jobProfiles = $status
            ? $this->jobProfileService->getByStatus($status)
            : $this->jobProfileService->getAll();

        return view('jobprofile::index', compact('jobProfiles', 'status'));
    }

    /**
     * Show the form for creating a new job profile.
     */
    public function create(): View
    {
        $user = auth()->user();

        // Obtener unidades organizacionales para el dropdown
        $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
        // Obtener códigos de posición
        $positionCodes = \Modules\JobProfile\Entities\PositionCode::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn($pc) => [$pc->id => $pc->code . ' - ' . $pc->title])
            ->toArray();

        // Verificar si el usuario es area-user
        $isAreaUser = $user->hasRole('area-user');

        // Obtener la unidad organizacional del usuario (la primaria y activa)
        $userOrganizationalUnit = null;
        if ($isAreaUser) {
            $userOrgUnit = \Modules\User\Entities\UserOrganizationUnit::where('user_id', $user->id)
                ->where('is_active', true)
                ->where('is_primary', true)
                ->first();

            if ($userOrgUnit) {
                $userOrganizationalUnit = $userOrgUnit->organization_unit_id;
            }
        }
        $educationOptions = EducationLevelEnum::selectOptions();

        // Si viene desde una convocatoria, cargar la información
        $jobPosting = null;
        if (request('job_posting_id')) {
            $jobPosting = \Modules\JobPosting\Entities\JobPosting::find(request('job_posting_id'));

            // Validar que la convocatoria existe y está en borrador
            if ($jobPosting && !$jobPosting->isDraft()) {
                return redirect()->route('jobprofile.profiles.create')
                    ->with('error', 'Solo se pueden agregar perfiles a convocatorias en estado borrador.');
            }
        }

        return view('jobprofile::create', compact(
            'organizationalUnits',
            'positionCodes',
            'isAreaUser',
            'userOrganizationalUnit',
            'educationOptions',
            'jobPosting'
        ));
    }

    /**
     * Store a newly created job profile.
     */
    public function store(StoreJobProfileRequest $request): RedirectResponse
    {
        try {
            $jobProfile = $this->jobProfileService->create(
                $request->except(['requirements', 'responsibilities']),
                $request->get('requirements', []),
                $request->get('responsibilities', [])
            );

            return redirect()
                ->route('jobprofile.profiles.show', $jobProfile->id)
                ->with('success', 'Perfil de puesto creado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al crear el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified job profile.
     */
    public function show(string $id): View
    {
        $jobProfile = $this->jobProfileService->findById($id);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        return view('jobprofile::show', compact('jobProfile'));
    }

    /**
     * Show the form for editing the specified job profile.
     */
    public function edit(string $id): View
    {
        $jobProfile = $this->jobProfileService->findById($id);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        if (!$jobProfile->canEdit()) {
            abort(403, 'No se puede editar este perfil en su estado actual.');
        }

        $user = auth()->user();

        // Unidades Organizacionales
        $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        // Position Codes
        $positionCodes = \Modules\JobProfile\Entities\PositionCode::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn($pc) => [$pc->id => $pc->code . ' - ' . $pc->title])
            ->toArray();

        // ¿Es usuário de área?
        $isAreaUser = $user->hasRole('area-user');

        // Unidad organizacional primaria del usuario
        $userOrganizationalUnit = null;
        if ($isAreaUser) {
            $userOrgUnit = \Modules\User\Entities\UserOrganizationUnit::where('user_id', $user->id)
                ->where('is_active', true)
                ->where('is_primary', true)
                ->first();

            if ($userOrgUnit) {
                $userOrganizationalUnit = $userOrgUnit->organization_unit_id;
            }
        }

        // Opciones de educación
        $educationOptions = EducationLevelEnum::selectOptions();

        // Convocatoria (job posting)
        $jobPosting = null;
        if ($jobProfile->job_posting_id) {
            $jobPosting = \Modules\JobPosting\Entities\JobPosting::find($jobProfile->job_posting_id);
        }

        return view('jobprofile::edit', compact(
            'jobProfile',
            'organizationalUnits',
            'positionCodes',
            'isAreaUser',
            'userOrganizationalUnit',
            'educationOptions',
            'jobPosting'
        ));
    }


    /**
     * Update the specified job profile.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        try {
            $jobProfile = $this->jobProfileService->update(
                $id,
                $request->except(['requirements', 'responsibilities'])
            );

            if ($request->has('requirements')) {
                $this->jobProfileService->updateRequirements($id, $request->get('requirements'));
            }

            if ($request->has('responsibilities')) {
                $this->jobProfileService->updateResponsibilities($id, $request->get('responsibilities'));
            }

            return redirect()
                ->route('jobprofile.profiles.show', $jobProfile->id)
                ->with('success', 'Perfil de puesto actualizado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified job profile.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->jobProfileService->delete($id);

            return redirect()
                ->route('jobprofile.index')
                ->with('success', 'Perfil de puesto eliminado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Submit a job profile for review.
     */
    public function submitForReview(string $id): RedirectResponse
    {
        $jobProfile = $this->jobProfileService->findById($id);

        if (!$jobProfile) {
            abort(404, 'Perfil de puesto no encontrado.');
        }

        // Verificar autorización usando policy
        $this->authorize('submitForReview', $jobProfile);

        try {
            $userId = auth()->id();
            $this->reviewService->submitForReview($id, $userId);

            return redirect()
                ->route('jobprofile.profiles.show', $id)
                ->with('success', 'Perfil enviado a revisión exitosamente. El equipo de RRHH lo revisará pronto.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar a revisión: ' . $e->getMessage());
        }
    }
}
