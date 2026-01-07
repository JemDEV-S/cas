<?php

namespace Modules\ApplicantPortal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\JobPosting\Services\JobPostingService;
use Modules\Application\Services\ApplicationService;
use Modules\JobProfile\Enums\EducationLevelEnum;

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
            ->get()
            ->map(function($profile) {
                // Formatear niveles educativos de manera profesional
                if ($profile->education_levels && is_array($profile->education_levels)) {
                    $profile->formatted_education_levels = EducationLevelEnum::formatMultiple($profile->education_levels);
                } else {
                    $profile->formatted_education_levels = 'No especificado';
                }

                // Formatear experiencia general
                if ($profile->general_experience_years) {
                    $profile->formatted_general_experience = $profile->general_experience_years->toHuman();
                } else {
                    $profile->formatted_general_experience = null;
                }

                // Formatear experiencia específica
                if ($profile->specific_experience_years) {
                    $profile->formatted_specific_experience = $profile->specific_experience_years->toHuman();
                } else {
                    $profile->formatted_specific_experience = null;
                }

                return $profile;
            });

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
            'organizationalUnit',
            'careers.career'
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

        // Recargar el usuario con todos sus datos (incluyendo birth_date, address, phone)
        $user->refresh();

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

        // 💎 Cargar catálogo de carreras ACTIVAS, agrupadas y ordenadas
        $academicCareers = \Modules\Application\Entities\AcademicCareer::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category_group');

        // 💎 Obtener carreras aceptadas desde tabla pivote (con equivalencias)
        $acceptedCareerIds = $jobProfile->getAcceptedCareerIds(includeEquivalences: true);

        // Obtener nombres de carreras aceptadas para mostrar al usuario
        $acceptedCareerNames = \Modules\Application\Entities\AcademicCareer::whereIn('id', $jobProfile->careers()->pluck('career_id'))
            ->pluck('name')
            ->toArray();

        // 💎 Preparar estructura inicial de cumplimiento de capacitaciones requeridas
        $requiredCoursesComplianceInitial = collect($jobProfile->required_courses ?? [])->map(function($course, $index) {
            return [
                'courseName' => $course,
                'status' => 'none', // 'exact', 'related', 'none'
                'institution' => '',
                'year' => '',
                'hours' => '',
                'relatedCourseName' => '',
                'relatedInstitution' => '',
                'relatedYear' => '',
                'relatedHours' => '',
                // Mantener por compatibilidad con datos antiguos
                'hasIt' => false,
                'isRelated' => false,
            ];
        })->values()->all();

        // 💎 Preparar estructura inicial de cumplimiento de conocimientos técnicos
        $knowledgeComplianceInitial = collect($jobProfile->knowledge_areas ?? [])->map(function($area, $index) {
            return [
                'area' => $area,
                'hasIt' => false
            ];
        })->values()->all();

        // 💎 Obtener opciones de niveles educativos desde el enum
        $educationLevels = \Modules\JobProfile\Enums\EducationLevelEnum::options();

        // 💎 Obtener el nivel mínimo requerido por el perfil (si existe)
        $minimumEducationLevel = null;
        if (!empty($jobProfile->education_levels)) {
            // Obtener el nivel mínimo de los requeridos
            $requiredLevels = collect($jobProfile->education_levels)
                ->map(fn($level) => \Modules\JobProfile\Enums\EducationLevelEnum::from($level))
                ->sortBy(fn($enum) => $enum->level());
            $minimumEducationLevel = $requiredLevels->first();
        }

        return view('applicantportal::job-postings.apply', compact(
            'posting',
            'jobProfile',
            'user',
            'academicCareers',
            'acceptedCareerIds',
            'acceptedCareerNames',
            'requiredCoursesComplianceInitial',
            'knowledgeComplianceInitial',
            'educationLevels',
            'minimumEducationLevel'
        ));
    }

    /**
     * Store a new application.
     */
    public function storeApplication(Request $request, string $postingId, string $profileId)
    {
        // 1. Validar datos del request
        $validated = $request->validate([
            'action' => 'required|in:submit,draft',
            'formData' => 'required|json',
        ]);

        $user = Auth::user();

        try {
            // 2. Parsear y validar datos del formulario
            $formData = json_decode($validated['formData'], true);

            if (!$formData) {
                throw new \Exception('Los datos del formulario no son válidos');
            }

            // 3. Validar fase actual
            $posting = $this->jobPostingService->getJobPostingById($postingId);
            $currentPhase = $this->jobPostingService->getCurrentPhase($postingId);

            if (!$currentPhase || !in_array($currentPhase->phase->code ?? '', ['PHASE_03_REGISTRATION', 'REGISTRATION'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Esta convocatoria no está en fase de registro');
            }

            // 4. Validar que no haya postulado a este perfil
            $profile = \Modules\JobProfile\Entities\JobProfile::findOrFail($profileId);
            $existingApp = \Modules\Application\Entities\Application::where('applicant_id', $user->id)
                ->whereHas('vacancy', fn($q) => $q->where('job_profile_id', $profileId))
                ->first();

            if ($existingApp) {
                return redirect()
                    ->back()
                    ->with('error', 'Ya has postulado a este perfil anteriormente');
            }

            // 5. Obtener vacante disponible
            $vacancy = $profile->vacancies()->where('status', 'available')->first();
            if (!$vacancy) {
                return redirect()
                    ->back()
                    ->with('error', 'No hay vacantes disponibles para este perfil');
            }

            // 6. Determinar estado según acción
            $status = $validated['action'] === 'submit'
                ? \Modules\Application\Enums\ApplicationStatus::SUBMITTED
                : \Modules\Application\Enums\ApplicationStatus::DRAFT;

            // 7. Iniciar transacción de base de datos
            return \DB::transaction(function () use ($user, $vacancy, $formData, $status, $request) {
                // 8. Crear ApplicationDTO
                $dto = new \Modules\Application\DTOs\ApplicationDTO(
                    applicantId: $user->id,
                    jobProfileVacancyId: $vacancy->id,
                    personalData: new \Modules\Application\DTOs\PersonalDataDTO(
                        fullName: $formData['personal']['fullName'] ?? ($user->first_name . ' ' . $user->last_name),
                        dni: $formData['personal']['dni'] ?? $user->dni,
                        birthDate: $formData['personal']['birthDate'] ?? $user->birth_date,
                        address: $formData['personal']['address'] ?? $user->address,
                        mobilePhone: $formData['personal']['phone'] ?? $user->phone,
                        email: $formData['personal']['email'] ?? $user->email,
                        phone: $formData['personal']['phone'] ?? $user->phone
                    ),
                    academics: $this->mapAcademics($formData['academics'] ?? []),
                    experiences: $this->mapExperiences($formData['experiences'] ?? []),
                    trainings: $this->mapTrainings($formData),
                    knowledge: $this->mapKnowledge($formData),
                    professionalRegistrations: $this->mapRegistrations($formData['registrations'] ?? []),
                    specialConditions: $this->mapSpecialConditions($formData['specialConditions'] ?? []),
                    termsAccepted: $formData['termsAccepted'] ?? false,
                    ipAddress: $request->ip()
                );

                // 9. Crear postulación
                $application = $this->applicationService->create($dto);

                // 10. Actualizar estado si es necesario
                if ($status === \Modules\Application\Enums\ApplicationStatus::DRAFT) {
                    $application->update(['status' => $status]);
                }

                // 11. Generar ficha de postulación PDF (solo si se envió, no si es borrador)
                if ($status === \Modules\Application\Enums\ApplicationStatus::SUBMITTED) {
                    // TODO: Implementar generación de PDF
                    // app(DocumentService::class)->generateFromTemplate(...)
                }

                // 12. Mensaje según acción
                $message = $status === \Modules\Application\Enums\ApplicationStatus::SUBMITTED
                    ? '¡Postulación enviada exitosamente!'
                    : 'Borrador guardado. Puedes completar y enviar después.';

                return redirect()
                    ->route('applicant.applications.show', $application->id)
                    ->with('success', $message);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error de validación - devolver con errores específicos
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Perfil o vacante no encontrada
            \Log::error('Perfil o vacante no encontrada', [
                'profile_id' => $profileId,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('applicant.job-postings.index')
                ->with('error', 'El perfil solicitado no existe o no está disponible');

        } catch (\Exception $e) {
            // Error genérico
            \Log::error('Error al crear postulación', [
                'user_id' => $user->id ?? null,
                'profile_id' => $profileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ocurrió un error al procesar tu postulación. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Mapear datos académicos al formato DTO (MEJORADO con career_id)
     */
    private function mapAcademics(array $academics): array
    {
        return array_map(function($academic) {
            return new \Modules\Application\DTOs\AcademicDTO(
                institutionName: $academic['institution'] ?? '',
                degreeType: $academic['degreeType'] ?? '',
                degreeTitle: $academic['degreeTitle'] ?? ($academic['careerField'] ?? ''),
                issueDate: $academic['year'] ?? date('Y'),
                careerField: $academic['careerField'] ?? '', // Mantener por compatibilidad
                careerId: $academic['careerId'] ?? null, // 💎 ID de la carrera del catálogo
                isRelatedCareer: $academic['isRelatedCareer'] ?? false, // 💎 NUEVO: Es carrera afín
                relatedCareerName: $academic['relatedCareerName'] ?? null, // 💎 NUEVO: Nombre de carrera afín
            );
        }, $academics);
    }

    /**
     * Mapear experiencias al formato DTO
     */
    private function mapExperiences(array $experiences): array
    {
        return array_map(function($exp) {
            // Si isCurrent es true, usar fecha actual como endDate
            $endDate = ($exp['isCurrent'] ?? false)
                ? date('Y-m-d')
                : ($exp['endDate'] ?? date('Y-m-d'));

            return new \Modules\Application\DTOs\ExperienceDTO(
                organization: $exp['organization'] ?? '',
                position: $exp['position'] ?? '',
                startDate: $exp['startDate'] ?? date('Y-m-d'),
                endDate: $endDate,
                isSpecific: $exp['isSpecific'] ?? false,
                isPublicSector: $exp['isPublicSector'] ?? false
            );
        }, $experiences);
    }

    /**
     * Mapear capacitaciones al formato DTO (MEJORADO con nuevo modelo)
     */
    private function mapTrainings(array $formData): array
    {
        $trainings = [];

        // 1. Procesar capacitaciones requeridas que cumplió
        if (isset($formData['requiredCoursesCompliance']) && is_array($formData['requiredCoursesCompliance'])) {
            foreach ($formData['requiredCoursesCompliance'] as $course) {
                // Solo incluir si tiene status 'exact' o 'related'
                if (($course['status'] ?? 'none') === 'exact') {
                    // Capacitación exacta
                    $trainings[] = new \Modules\Application\DTOs\TrainingDTO(
                        institution: $course['institution'] ?? '',
                        courseName: $course['courseName'] ?? '',
                        academicHours: (int)($course['hours'] ?? 0),
                        startDate: $course['year'] ? $course['year'] . '-01-01' : null,
                        endDate: $course['year'] ? $course['year'] . '-12-31' : null
                    );
                } elseif (($course['status'] ?? 'none') === 'related') {
                    // Capacitación afín/relacionada (agregar nota en el nombre)
                    $courseName = ($course['relatedCourseName'] ?? '') . ' (Afín a: ' . ($course['courseName'] ?? '') . ')';
                    $trainings[] = new \Modules\Application\DTOs\TrainingDTO(
                        institution: $course['relatedInstitution'] ?? '',
                        courseName: $courseName,
                        academicHours: (int)($course['relatedHours'] ?? 0),
                        startDate: $course['relatedYear'] ? $course['relatedYear'] . '-01-01' : null,
                        endDate: $course['relatedYear'] ? $course['relatedYear'] . '-12-31' : null
                    );
                }
            }
        }

        // 2. Procesar capacitaciones adicionales
        if (isset($formData['additionalTrainings']) && is_array($formData['additionalTrainings'])) {
            foreach ($formData['additionalTrainings'] as $training) {
                // Solo agregar si tiene nombre de curso
                if (!empty($training['courseName'])) {
                    // Convertir certificationDate (YYYY-MM) a rango de fechas
                    $certDate = $training['certificationDate'] ?? null;
                    $startDate = $certDate ? $certDate . '-01' : null;
                    $endDate = $certDate ? $certDate . '-' . date('t', strtotime($certDate . '-01')) : null;

                    $trainings[] = new \Modules\Application\DTOs\TrainingDTO(
                        institution: $training['institution'] ?? '',
                        courseName: $training['courseName'] ?? '',
                        academicHours: (int)($training['hours'] ?? 0),
                        startDate: $startDate,
                        endDate: $endDate
                    );
                }
            }
        }

        return $trainings;
    }

    /**
     * Mapear conocimientos al formato DTO (MEJORADO con nuevo modelo)
     */
    private function mapKnowledge(array $formData): array
    {
        $knowledge = [];

        // 1. Procesar conocimientos requeridos que cumple
        if (isset($formData['knowledgeCompliance']) && is_array($formData['knowledgeCompliance'])) {
            foreach ($formData['knowledgeCompliance'] as $k) {
                // Solo incluir si marcó que lo tiene
                if (($k['hasIt'] ?? false) === true) {
                    $knowledge[] = new \Modules\Application\DTOs\KnowledgeDTO(
                        knowledgeName: $k['area'] ?? '',
                        proficiencyLevel: 'BASICO' // Por defecto, se puede expandir más adelante
                    );
                }
            }
        }

        // 2. Procesar otros conocimientos (texto libre)
        if (!empty($formData['otherKnowledge'])) {
            // Dividir por comas o saltos de línea
            $otherAreas = preg_split('/[,\n]+/', $formData['otherKnowledge']);
            foreach ($otherAreas as $area) {
                $area = trim($area);
                if (!empty($area)) {
                    $knowledge[] = new \Modules\Application\DTOs\KnowledgeDTO(
                        knowledgeName: $area,
                        proficiencyLevel: 'BASICO'
                    );
                }
            }
        }

        return $knowledge;
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
