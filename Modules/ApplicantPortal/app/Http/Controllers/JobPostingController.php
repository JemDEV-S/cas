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
            ->with('jobProfile.jobPosting')  // ← ACTUALIZADO: relación directa
            ->get();

        $appliedPostingIds = $userApplications->pluck('jobProfile.job_posting_id')->unique()->toArray();  // ← ACTUALIZADO

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
            ->whereHas('jobProfile', fn($q) => $q->where('job_posting_id', $id))
            ->with('jobProfile')
            ->get();

        $appliedProfileIds = $userApplications->pluck('jobProfile.id')->toArray();
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

        // ← ACTUALIZADO: verificar por job_profile_id directamente
        $existingApplication = \Modules\Application\Entities\Application::where('applicant_id', $user->id)
            ->where('job_profile_id', $profileId)
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

            // ← ACTUALIZADO: verificar por job_profile_id directamente
            $existingApp = \Modules\Application\Entities\Application::where('applicant_id', $user->id)
                ->where('job_profile_id', $profileId)
                ->first();

            if ($existingApp) {
                return redirect()
                    ->back()
                    ->with('error', 'Ya has postulado a este perfil anteriormente');
            }

            // 5. ← ACTUALIZADO: Ya no se asigna vacante al postular, solo se verifica que haya disponibles
            $availableVacancies = $profile->vacancies()->where('status', 'available')->count();
            if ($availableVacancies === 0) {
                return redirect()
                    ->back()
                    ->with('error', 'No hay vacantes disponibles para este perfil');
            }

            // 6. Determinar estado según acción
            $status = $validated['action'] === 'submit'
                ? \Modules\Application\Enums\ApplicationStatus::SUBMITTED
                : \Modules\Application\Enums\ApplicationStatus::DRAFT;

            // 7. Iniciar transacción de base de datos
            return \DB::transaction(function () use ($user, $profileId, $formData, $status, $request) {
                // 8. ← ACTUALIZADO: Crear ApplicationDTO con job_profile_id
                $dto = new \Modules\Application\DTOs\ApplicationDTO(
                    applicantId: $user->id,
                    jobProfileId: $profileId,  // ← ACTUALIZADO: ahora es jobProfileId directamente
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
                    $this->generateApplicationSheet($application);
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
                registrationType: 'COLEGIATURA',
                registrationNumber: $registrations['colegiatura']['number'],
                issuingEntity: $registrations['colegiatura']['college'] ?? null
            );
        }

        // OSCE
        if (!empty($registrations['osce'])) {
            $result[] = new \Modules\Application\DTOs\ProfessionalRegistrationDTO(
                registrationType: 'OSCE',
                registrationNumber: $registrations['osce']
            );
        }

        // Licencia de conducir
        if (!empty($registrations['license']['number'])) {
            $result[] = new \Modules\Application\DTOs\ProfessionalRegistrationDTO(
                registrationType: 'LICENCIA_CONDUCIR',
                registrationNumber: $registrations['license']['number'],
                issuingEntity: $registrations['license']['category'] ?? null,
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

    /**
     * Generar ficha de postulación en PDF
     */
    private function generateApplicationSheet(\Modules\Application\Entities\Application $application): void
    {
        try {
            // Cargar todas las relaciones necesarias
            $application->load([
                'applicant',
                'jobProfile.jobPosting',
                'academics.career',
                'experiences',
                'trainings',
                'knowledge',
                'professionalRegistrations',
                'specialConditions'
            ]);

            // Obtener el template
            $template = \Modules\Document\Entities\DocumentTemplate::where('code', 'TPL_APPLICATION_SHEET')
                ->where('status', 'active')
                ->first();

            if (!$template) {
                \Log::warning('Template TPL_APPLICATION_SHEET no encontrado', [
                    'application_id' => $application->id,
                ]);
                return;
            }

            // Preparar datos para el documento
            $data = $this->prepareApplicationSheetData($application);

            // Generar el documento usando el servicio
            $documentService = app(\Modules\Document\Services\DocumentService::class);
            $document = $documentService->generateFromTemplate(
                $template,
                $application,
                $data,
                $application->applicant_id
            );

            \Log::info('Ficha de postulación generada exitosamente', [
                'application_id' => $application->id,
                'document_id' => $document->id,
                'document_code' => $document->code,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar ficha de postulación', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Preparar datos de la postulación para el template
     */
    private function prepareApplicationSheetData(\Modules\Application\Entities\Application $application): array
    {
        $jobProfile = $application->jobProfile;
        $jobPosting = $jobProfile->jobPosting;

        // Calcular edad
        $age = null;
        if ($application->birth_date) {
            $birthDate = \Carbon\Carbon::parse($application->birth_date);
            $age = $birthDate->age;
        }

        return [
            'title' => 'Ficha de Postulación - ' . $application->code,

            // Datos de la postulación
            'application_code' => $application->code,
            'application_date' => $application->application_date?->format('d/m/Y'),

            // Datos de la convocatoria y perfil
            'job_posting_title' => $jobPosting->title ?? 'N/A',
            'job_posting_code' => $jobPosting->code ?? 'N/A',
            'job_profile_name' => $jobProfile->profile_name ?? 'N/A',
            'profile_code' => $jobProfile->code ?? 'N/A',
            // ← REMOVIDO: vacancy_code (se asigna después de la evaluación)

            // Datos personales
            'full_name' => $application->full_name,
            'dni' => $application->dni,
            'birth_date' => $application->birth_date?->format('d/m/Y'),
            'age' => $age,
            'email' => $application->email,
            'phone' => $application->phone,
            'mobile_phone' => $application->mobile_phone,
            'address' => $application->address,

            // Formación académica
            'academics' => $application->academics->map(function ($academic) {
                // Convertir el degree_type al label del enum si es posible
                $degreeTypeLabel = $academic->degree_type;
                try {
                    if ($academic->degree_type) {
                        $enum = \Modules\JobProfile\Enums\EducationLevelEnum::from($academic->degree_type);
                        $degreeTypeLabel = $enum->label();
                    }
                } catch (\ValueError $e) {
                    // Si el valor no es válido en el enum, usar el valor original
                    $degreeTypeLabel = $academic->degree_type;
                }

                return [
                    'institution_name' => $academic->institution_name,
                    'degree_type' => $academic->degree_type,
                    'degree_type_label' => $degreeTypeLabel,
                    'career_field' => $academic->career?->name ?? $academic->career_field,
                    'degree_title' => $academic->degree_title,
                    'issue_date' => $academic->issue_date?->format('Y'),
                    'is_related_career' => $academic->is_related_career,
                    'related_career_name' => $academic->related_career_name,
                ];
            })->toArray(),

            // Experiencia laboral
            'experiences' => $application->experiences->map(function ($experience) {
                return [
                    'organization' => $experience->organization,
                    'position' => $experience->position,
                    'start_date' => $experience->start_date?->format('d/m/Y'),
                    'end_date' => $experience->end_date?->format('d/m/Y'),
                    'duration_days' => $experience->duration_days,
                    'is_specific' => $experience->is_specific,
                    'is_public_sector' => $experience->is_public_sector,
                ];
            })->toArray(),

            // Calcular totales de experiencia
            'total_general_experience' => $this->calculateExperienceSummary(
                $application->experiences->where('is_specific', false)
            ),
            'total_specific_experience' => $this->calculateExperienceSummary(
                $application->experiences->where('is_specific', true)
            ),

            // Capacitaciones
            'trainings' => $application->trainings->map(function ($training) {
                return [
                    'institution' => $training->institution,
                    'course_name' => $training->course_name,
                    'academic_hours' => $training->academic_hours,
                    'start_date' => $training->start_date?->format('Y-m-d'),
                ];
            })->toArray(),

            // Conocimientos
            'knowledge' => $application->knowledge->map(function ($k) {
                return [
                    'knowledge_name' => $k->knowledge_name,
                    'proficiency_level' => $k->proficiency_level,
                ];
            })->toArray(),

            // Registros profesionales
            'professional_registrations' => $application->professionalRegistrations->map(function ($reg) {
                return [
                    'type' => $reg->registration_type,
                    'number' => $reg->registration_number,
                    'institution' => $reg->issuing_entity,
                    'category' => null, // No hay campo category en el modelo
                    'expiry_date' => $reg->expiry_date?->format('d/m/Y'),
                ];
            })->toArray(),

            // Condiciones especiales
            'special_conditions' => $application->specialConditions->map(function ($condition) {
                return [
                    'type' => $condition->type,
                    'bonus_percentage' => $condition->bonus_percentage,
                ];
            })->toArray(),

            // Información adicional
            'ip_address' => $application->ip_address,
            'generation_date' => now()->format('d/m/Y'),
            'generation_time' => now()->format('H:i:s'),
        ];
    }

    /**
     * Calcular resumen de experiencia (total en años, meses y días)
     */
    private function calculateExperienceSummary($experiences): array
    {
        $totalDays = $experiences->sum('duration_days');

        if ($totalDays <= 0) {
            return [
                'total_days' => 0,
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'formatted' => '0 días',
            ];
        }

        $years = floor($totalDays / 365);
        $months = floor(($totalDays % 365) / 30);
        $days = $totalDays % 30;

        $parts = [];
        if ($years > 0) $parts[] = "{$years} año" . ($years > 1 ? 's' : '');
        if ($months > 0) $parts[] = "{$months} mes" . ($months > 1 ? 'es' : '');
        if ($days > 0) $parts[] = "{$days} día" . ($days > 1 ? 's' : '');

        return [
            'total_days' => $totalDays,
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'formatted' => implode(', ', $parts) ?: '0 días',
        ];
    }
}
