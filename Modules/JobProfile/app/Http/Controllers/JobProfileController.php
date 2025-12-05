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
use Modules\Organization\Entities\OrganizationalUnit;


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
        // Verificar que tenga algún permiso de visualización
        $this->authorize('viewAny', \Modules\JobProfile\Entities\JobProfile::class);

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
        // Verificar autorización (incluye validación de fechas)
        $this->authorize('create', \Modules\JobProfile\Entities\JobProfile::class);

        $user = auth()->user();

        // Cargar roles del usuario para verificación
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        // Verificar si el usuario es area-user de forma más directa
        $isAreaUser = $user->roles()->where('slug', 'area-user')->exists();

        // Obtener la unidad organizacional del usuario (la primaria y activa)
        $userOrganizationalUnit = null;
        $organizationalUnits = [];

        if ($isAreaUser) {
            $primaryUnit = $user->primaryOrganizationUnit();
            if ($primaryUnit) {
                $userOrganizationalUnit = $primaryUnit->id;

                // Obtener la unidad del usuario y todas sus descendientes
                $allowedUnitIds = $this->getAllDescendantIds($primaryUnit);

                // Filtrar solo las unidades permitidas para el area-user
                $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::whereIn('id', $allowedUnitIds)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
            }
        } else {
            // Para usuarios no area-user, mostrar todas las unidades
            $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }

        // Obtener códigos de posición
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
            'positionCodesData',
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
        // Verificar autorización (incluye validación de fechas)
        $this->authorize('create', \Modules\JobProfile\Entities\JobProfile::class);

        $validatedData = $request->validated();

        try {
            // Excluir campos específicos de los datos validados
            $dataToCreate = collect($validatedData)
                ->except(['requirements', 'responsibilities'])
                ->toArray();

            // Si no viene requesting_unit_id, usar el mismo valor de organizational_unit_id
            if (empty($dataToCreate['requesting_unit_id']) && !empty($dataToCreate['organizational_unit_id'])) {
                $dataToCreate['requesting_unit_id'] = $dataToCreate['organizational_unit_id'];
            }

            $jobProfile = $this->jobProfileService->create(
                $dataToCreate,
                $validatedData['requirements'] ?? [],
                $validatedData['responsibilities'] ?? []
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
     * El Route Model Binding ya aplica el scope de visibilidad automáticamente
     */
    public function show(\Modules\JobProfile\Entities\JobProfile $profile): View
    {
        // El Route Model Binding ya verificó los permisos, pero agregamos authorize por seguridad
        $this->authorize('view', $profile);

        // Cargar relaciones necesarias
        $profile->load([
            'positionCode',
            'organizationalUnit',
            'requestingUnit',
            'requestedBy',
            'reviewedBy',
            'approvedBy',
            'requirements',
            'responsibilities',
            'vacancies',
            'history'
        ]);

        return view('jobprofile::show', ['jobProfile' => $profile]);
    }

    /**
     * Show the form for editing the specified job profile.
     * El Route Model Binding ya aplica el scope de visibilidad automáticamente
     */
    public function edit(\Modules\JobProfile\Entities\JobProfile $profile): View
    {
        // Verificar que el usuario tenga permiso para editar este perfil
        $this->authorize('update', $profile);

        if (!$profile->canEdit()) {
            abort(403, 'No se puede editar este perfil en su estado actual.');
        }

        $user = auth()->user();

        // Cargar roles del usuario para verificación
        $user->load('roles');

        // ¿Es usuário de área?
        $isAreaUser = $user->hasRole('area-user');

        // Unidad organizacional primaria del usuario
        $userOrganizationalUnit = null;
        $organizationalUnits = [];

        if ($isAreaUser) {
            $primaryUnit = $user->primaryOrganizationUnit();
            if ($primaryUnit) {
                $userOrganizationalUnit = $primaryUnit->id;

                // Obtener la unidad del usuario y todas sus descendientes
                $allowedUnitIds = $this->getAllDescendantIds($primaryUnit);

                // Filtrar solo las unidades permitidas para el area-user
                $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::whereIn('id', $allowedUnitIds)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->pluck('name', 'id')
                    ->toArray();
            }
        } else {
            // Para usuarios no area-user, mostrar todas las unidades
            $organizationalUnits = \Modules\Organization\Entities\OrganizationalUnit::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }

        // Position Codes
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

        // Opciones de educación
        $educationOptions = EducationLevelEnum::selectOptions();

        // Convocatoria (job posting)
        $jobPosting = null;
        if ($profile->job_posting_id) {
            $jobPosting = \Modules\JobPosting\Entities\JobPosting::find($profile->job_posting_id);
        }

        return view('jobprofile::edit', [
            'jobProfile' => $profile,
            'organizationalUnits' => $organizationalUnits,
            'positionCodes' => $positionCodes,
            'positionCodesData' => $positionCodesData,
            'isAreaUser' => $isAreaUser,
            'userOrganizationalUnit' => $userOrganizationalUnit,
            'educationOptions' => $educationOptions,
            'jobPosting' => $jobPosting
        ]);
    }


    /**
     * Update the specified job profile.
     * El Route Model Binding ya aplica el scope de visibilidad automáticamente
     */
    public function update(Request $request, \Modules\JobProfile\Entities\JobProfile $profile): RedirectResponse
    {
        // Verificar que el usuario tenga permiso para actualizar este perfil
        $this->authorize('update', $profile);

        try {
            $updatedProfile = $this->jobProfileService->update(
                $profile->id,
                $request->except(['requirements', 'responsibilities'])
            );

            if ($request->has('requirements')) {
                $this->jobProfileService->updateRequirements($profile->id, $request->get('requirements'));
            }

            if ($request->has('responsibilities')) {
                $this->jobProfileService->updateResponsibilities($profile->id, $request->get('responsibilities'));
            }

            return redirect()
                ->route('jobprofile.profiles.show', $updatedProfile->id)
                ->with('success', 'Perfil de puesto actualizado exitosamente.');
        } catch (BusinessRuleException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified job profile.
     * El Route Model Binding ya aplica el scope de visibilidad automáticamente
     */
    public function destroy(\Modules\JobProfile\Entities\JobProfile $profile): RedirectResponse
    {
        // Verificar que el usuario tenga permiso para eliminar este perfil
        $this->authorize('delete', $profile);

        try {
            $this->jobProfileService->delete($profile->id);

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
     * El Route Model Binding ya aplica el scope de visibilidad automáticamente
     */
    public function submitForReview(\Modules\JobProfile\Entities\JobProfile $profile): RedirectResponse
    {
        // Verificar autorización usando policy
        $this->authorize('submitForReview', $profile);

        try {
            $userId = auth()->id();
            $this->reviewService->submitForReview($profile->id, $userId);

            return redirect()
                ->route('jobprofile.profiles.show', $profile->id)
                ->with('success', 'Perfil enviado a revisión exitosamente. El equipo de RRHH lo revisará pronto.');
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar a revisión: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene recursivamente todos los IDs de descendientes de una unidad organizacional
     * Incluye la unidad misma y todos sus hijos directos e indirectos
     */
    private function getAllDescendantIds($unit): array
    {
        $ids = [$unit->id];

        $children = $unit->children;

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getAllDescendantIds($child));
        }

        return $ids;
    }
}
