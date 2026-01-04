<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\JobPosting\Services\JobPostingService;
use Modules\Application\Services\ApplicationService;

class JobPostingController extends Controller
{
    public function __construct(
        protected JobPostingService $jobPostingService,
        protected ApplicationService $applicationService
    ) {}

    /**
     * Display a listing of active job postings.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        // Obtener solo convocatorias PUBLICADAS con información completa
        $postings = \Modules\JobPosting\Entities\JobPosting::where('status', \Modules\JobPosting\Enums\JobPostingStatusEnum::PUBLICADA)
            ->with(['jobProfiles' => fn($q) => $q->where('status', 'active')])
            ->with('schedules.phase') // Cargar fase actual - relación correcta
            ->withCount('jobProfiles')
            ->when($search, fn($q) =>
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->latest('published_at')
            ->paginate(10);

        // Obtener postulaciones del usuario agrupadas por convocatoria
        $user = Auth::user();
        $userApplications = \Modules\Application\Entities\Application::where('applicant_id', $user->id)
            ->with('vacancy.jobProfile.jobPosting')
            ->get();

        $appliedPostingIds = $userApplications->pluck('vacancy.jobProfile.job_posting_id')->unique()->toArray();

        // Agregar fase actual a cada convocatoria
        $postings->getCollection()->transform(function ($posting) {
            $currentPhase = $posting->getCurrentPhase();
            $posting->current_phase = $currentPhase?->phase; // Usar relación phase
            return $posting;
        });

        return view('applicantportal::job-postings.index', compact(
            'postings',
            'appliedPostingIds',
            'search'
        ));
    }

    /**
     * Display the specified job posting with all details.
     */
    public function show(string $id)
    {
        // Cargar convocatoria con cronograma
        $posting = \Modules\JobPosting\Entities\JobPosting::with(['schedules.phase'])->findOrFail($id);
        $currentPhase = $posting->getCurrentPhase();

        // Obtener perfiles activos con relaciones necesarias
        $jobProfiles = \Modules\JobProfile\Entities\JobProfile::where('job_posting_id', $id)
            ->where('status', 'active')
            ->with([
                'positionCode',
                'requestingUnit',
                'organizationalUnit',
                'vacancies' => fn($q) => $q->where('status', 'available'),
                'requirements',
                'requirements.competence'
            ])
            ->get();

        // Verificar postulaciones del usuario por PERFIL
        $user = Auth::user();
        $userApplications = \Modules\Application\Entities\Application::where('applicant_id', $user->id)
            ->whereHas('vacancy.jobProfile', fn($q) => $q->where('job_posting_id', $id))
            ->with('vacancy.jobProfile')
            ->get();

        $appliedProfileIds = $userApplications->pluck('vacancy.jobProfile.id')->toArray();
        $hasApplied = $userApplications->count() > 0;

        // Verificar si puede postular (fase de registro)
        $canApply = $currentPhase && in_array($currentPhase->phase?->code ?? '', ['PHASE_03_REGISTRATION', 'REGISTRATION']);

        return view('applicantportal::job-postings.show', compact(
            'posting',
            'jobProfiles',
            'hasApplied',
            'userApplications',
            'appliedProfileIds',
            'currentPhase',
            'canApply'
        ));
    }

    /**
     * Show application form for a specific job profile.
     */
    public function apply(string $postingId, string $profileId)
    {
        $posting = $this->jobPostingService->getJobPostingById($postingId);

        // Get job profile with all related data
        $jobProfile = \Modules\JobProfile\Entities\JobProfile::with([
            'positionCode',
            'requestingUnit',
            'organizationalUnit'
        ])->findOrFail($profileId);

        // Check if posting is in registration phase
        $currentPhase = $this->jobPostingService->getCurrentPhase($postingId);
        if (!$currentPhase || !in_array($currentPhase->phase?->code ?? '', ['PHASE_03_REGISTRATION', 'REGISTRATION'])) {
            return redirect()
                ->route('applicant.job-postings.show', $postingId)
                ->with('error', 'Esta convocatoria no está en fase de registro.');
        }

        // Check if user has already applied to this profile
        $user = Auth::user();
        $existingApplication = \Modules\Application\Entities\Application::where('applicant_id', $user->id)
            ->whereHas('vacancy', function($q) use ($profileId) {
                $q->where('job_profile_id', $profileId);
            })
            ->first();

        if ($existingApplication) {
            return redirect()
                ->route('applicant.applications.show', $existingApplication->id)
                ->with('info', 'Ya has postulado a este perfil. Puedes editar tu postulación si está en estado Borrador.');
        }

        return view('applicantportal::job-postings.apply', compact(
            'posting',
            'jobProfile'
        ));
    }

    /**
     * Store a new application.
     */
    public function storeApplication(Request $request, string $postingId, string $profileId)
    {
        $user = Auth::user();

        try {
            // 1. Validar fase actual
            $posting = $this->jobPostingService->getJobPostingById($postingId);
            $currentPhase = $this->jobPostingService->getCurrentPhase($postingId);

            if (!$currentPhase || !in_array($currentPhase->code ?? '', ['PHASE_03_REGISTRATION', 'REGISTRATION'])) {
                return redirect()
                    ->back()
                    ->with('error', 'No está en fase de registro');
            }

            // 2. Validar que no haya postulado a este perfil
            $profile = \Modules\JobProfile\Entities\JobProfile::findOrFail($profileId);
            $existingApp = \Modules\Application\Entities\Application::where('applicant_id', $user->id)
                ->whereHas('vacancy', fn($q) => $q->where('job_profile_id', $profileId))
                ->first();

            if ($existingApp) {
                return redirect()
                    ->back()
                    ->with('error', 'Ya postulaste a este perfil');
            }

            // 3. Obtener vacante disponible
            $vacancy = $profile->vacancies()->where('status', 'available')->first();
            if (!$vacancy) {
                return redirect()
                    ->back()
                    ->with('error', 'No hay vacantes disponibles');
            }

            // 4. Determinar estado según acción
            $status = $request->action === 'submit'
                ? \Modules\Application\Enums\ApplicationStatus::SUBMITTED
                : \Modules\Application\Enums\ApplicationStatus::DRAFT;

            // 5. Parsear datos del formulario
            $formData = json_decode($request->formData, true);

            // 6. Crear ApplicationDTO
            $dto = new \Modules\Application\DTOs\ApplicationDTO(
                applicantId: $user->id,
                jobProfileVacancyId: $vacancy->id,
                personalData: new \Modules\Application\DTOs\PersonalDataDTO(
                    fullName: $formData['personal']['fullName'] ?? $user->name,
                    dni: $formData['personal']['dni'] ?? $user->dni,
                    birthDate: $formData['personal']['birthDate'] ?? null,
                    address: $formData['personal']['address'] ?? '',
                    mobilePhone: $formData['personal']['mobilePhone'] ?? '',
                    email: $formData['personal']['email'] ?? $user->email,
                    phone: $formData['personal']['phone'] ?? null
                ),
                academics: $this->mapAcademics($formData['academics'] ?? []),
                experiences: $this->mapExperiences($formData['experiences'] ?? []),
                trainings: $this->mapTrainings($formData['trainings'] ?? []),
                knowledge: $this->mapKnowledge($formData['knowledge'] ?? []),
                professionalRegistrations: $this->mapRegistrations($formData['registrations'] ?? []),
                specialConditions: $this->mapSpecialConditions($formData['specialConditions'] ?? []),
                termsAccepted: $formData['termsAccepted'] ?? false,
                ipAddress: $request->ip()
            );

            // 7. Crear postulación
            $application = $this->applicationService->create($dto);

            // 8. Actualizar estado si es necesario
            if ($status === \Modules\Application\Enums\ApplicationStatus::DRAFT) {
                $application->update(['status' => $status]);
            }

            // 9. Generar ficha de postulación PDF (solo si se envió, no si es borrador)
            if ($status === \Modules\Application\Enums\ApplicationStatus::SUBMITTED) {
                // TODO: Implementar generación de PDF
                // app(DocumentService::class)->generateFromTemplate(...)
            }

            // 10. Mensaje según acción
            $message = $status === \Modules\Application\Enums\ApplicationStatus::SUBMITTED
                ? '¡Postulación enviada exitosamente!'
                : 'Borrador guardado. Puedes completar y enviar después.';

            return redirect()
                ->route('applicant.applications.show', $application->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Error al crear postulación: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al enviar la postulación: ' . $e->getMessage());
        }
    }

    /**
     * Mapear datos académicos al formato DTO
     */
    private function mapAcademics(array $academics): array
    {
        return array_map(function($academic) {
            return new \Modules\Application\DTOs\AcademicDTO(
                degreeType: $academic['degreeType'] ?? '',
                institution: $academic['institution'] ?? '',
                careerField: $academic['careerField'] ?? '',
                year: $academic['year'] ?? null
            );
        }, $academics);
    }

    /**
     * Mapear experiencias al formato DTO
     */
    private function mapExperiences(array $experiences): array
    {
        return array_map(function($exp) {
            return new \Modules\Application\DTOs\ExperienceDTO(
                organization: $exp['organization'] ?? '',
                position: $exp['position'] ?? '',
                startDate: $exp['startDate'] ?? null,
                endDate: $exp['endDate'] ?? null,
                isCurrent: $exp['isCurrent'] ?? false,
                isPublicSector: $exp['isPublicSector'] ?? false,
                isSpecific: $exp['isSpecific'] ?? false,
                description: $exp['description'] ?? ''
            );
        }, $experiences);
    }

    /**
     * Mapear capacitaciones al formato DTO
     */
    private function mapTrainings(array $trainings): array
    {
        return array_map(function($training) {
            return new \Modules\Application\DTOs\TrainingDTO(
                courseName: $training['courseName'] ?? '',
                institution: $training['institution'] ?? '',
                hours: $training['hours'] ?? 0,
                certificationDate: $training['certificationDate'] ?? null
            );
        }, $trainings);
    }

    /**
     * Mapear conocimientos al formato DTO
     */
    private function mapKnowledge(array $knowledge): array
    {
        return array_map(function($k) {
            return new \Modules\Application\DTOs\KnowledgeDTO(
                area: $k['area'] ?? '',
                level: $k['level'] ?? ''
            );
        }, $knowledge);
    }

    /**
     * Mapear registros profesionales al formato DTO
     */
    private function mapRegistrations(array $registrations): array
    {
        $result = [];

        // Colegiatura
        if (!empty($registrations['colegiatura']['number'])) {
            $result[] = new \Modules\Application\DTOs\ProfessionalRegistrationDTO(
                type: 'COLEGIATURA',
                number: $registrations['colegiatura']['number'],
                institution: $registrations['colegiatura']['college'] ?? null
            );
        }

        // OSCE
        if (!empty($registrations['osce'])) {
            $result[] = new \Modules\Application\DTOs\ProfessionalRegistrationDTO(
                type: 'OSCE',
                number: $registrations['osce']
            );
        }

        // Licencia de conducir
        if (!empty($registrations['license']['number'])) {
            $result[] = new \Modules\Application\DTOs\ProfessionalRegistrationDTO(
                type: 'LICENCIA_CONDUCIR',
                number: $registrations['license']['number'],
                category: $registrations['license']['category'] ?? null,
                expiryDate: $registrations['license']['expiryDate'] ?? null
            );
        }

        return $result;
    }

    /**
     * Mapear condiciones especiales al formato DTO
     */
    private function mapSpecialConditions(array $conditions): array
    {
        $result = [];

        if ($conditions['disability'] ?? false) {
            $result[] = new \Modules\Application\DTOs\SpecialConditionDTO(
                type: 'DISCAPACIDAD',
                bonusPercentage: 15
            );
        }

        if ($conditions['veteran'] ?? false) {
            $result[] = new \Modules\Application\DTOs\SpecialConditionDTO(
                type: 'LICENCIADO_FFAA',
                bonusPercentage: 10
            );
        }

        if ($conditions['athlete'] ?? false) {
            $result[] = new \Modules\Application\DTOs\SpecialConditionDTO(
                type: 'DEPORTISTA_DESTACADO',
                bonusPercentage: 5
            );
        }

        if ($conditions['qualifiedAthlete'] ?? false) {
            $result[] = new \Modules\Application\DTOs\SpecialConditionDTO(
                type: 'DEPORTISTA_CALIFICADO',
                bonusPercentage: 3
            );
        }

        return $result;
    }
}
