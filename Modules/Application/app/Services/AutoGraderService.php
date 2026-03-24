<?php

namespace Modules\Application\Services;

use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;
use Modules\Core\ValueObjects\ExperienceDuration;

/**
 * Servicio para evaluación automática de elegibilidad
 *
 * Compara los datos de la postulación contra los requisitos del JobProfile
 * y determina automáticamente si es APTO o NO_APTO
 */
class AutoGraderService
{
    /**
     * Formatea un valor decimal de experiencia a texto legible
     * Ejemplo: 0.5 -> "6 meses", 2.5 -> "2 años, 6 meses"
     *
     * NOTA: El valor decimal representa años (0.5 = 6 meses, 1.0 = 1 año)
     *
     * @param float $decimalYears Valor decimal de años
     * @param bool $includeDays Si true, incluye días en el formato (para experiencia acreditada)
     */
    private function formatExperience(float $decimalYears, bool $includeDays = false): string
    {
        // Usar el mismo método que EligibilityCalculatorService para consistencia
        $totalDays = (int) round($decimalYears * 365);

        $years = (int) floor($totalDays / 365);
        $remainingDays = $totalDays % 365;
        $months = (int) floor($remainingDays / 30);
        $days = $remainingDays % 30;

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} " . ($years === 1 ? 'año' : 'años');
        }
        if ($months > 0) {
            $parts[] = "{$months} " . ($months === 1 ? 'mes' : 'meses');
        }
        // Solo mostrar días si se solicita explícitamente o si es el único valor
        if ($includeDays && $days > 0) {
            $parts[] = "{$days} " . ($days === 1 ? 'día' : 'días');
        }

        if (empty($parts)) {
            return 'Sin experiencia';
        }

        return implode(', ', $parts);
    }
    public function __construct(
        private EligibilityCalculatorService $eligibilityCalculator,
        private ?CareerMatcherService $careerMatcher = null
    ) {
        // Lazy initialization del CareerMatcherService si no se inyecta
        $this->careerMatcher = $careerMatcher ?? app(CareerMatcherService::class);
    }

    /**
     * Evaluar elegibilidad de una postulación
     *
     * @param Application $application
     * @return array ['is_eligible' => bool, 'reasons' => array, 'details' => array]
     */
    public function evaluateEligibility(Application $application): array
    {
        // ← ACTUALIZADO: obtener jobProfile directamente
        $jobProfile = $application->jobProfile;

        $results = [
            'is_eligible' => true,
            'reasons' => [],
            'details' => [],
            'has_pending_ia' => false,
        ];

        // 1. Validar Formación Académica
        $academicResult = $this->validateAcademics($application, $jobProfile);
        $results['details']['academics'] = $academicResult;
        if (!$academicResult['passed']) {
            $results['is_eligible'] = false;
            $results['reasons'][] = $academicResult['reason'];
        }
        if ($academicResult['pending_ia'] ?? false) {
            $results['has_pending_ia'] = true;
        }

        // 2. Validar Experiencia General
        $generalExpResult = $this->validateGeneralExperience($application, $jobProfile);
        $results['details']['general_experience'] = $generalExpResult;
        if (!$generalExpResult['passed']) {
            $results['is_eligible'] = false;
            $results['reasons'][] = $generalExpResult['reason'];
        }

        // 3. Validar Experiencia Específica
        $specificExpResult = $this->validateSpecificExperience($application, $jobProfile);
        $results['details']['specific_experience'] = $specificExpResult;
        if (!$specificExpResult['passed']) {
            $results['is_eligible'] = false;
            $results['reasons'][] = $specificExpResult['reason'];
        }

        // 4. Validar Colegiatura (si es requerida)
        if ($jobProfile?->colegiatura_required) {
            $registryResult = $this->validateProfessionalRegistry($application, $jobProfile);
            $results['details']['professional_registry'] = $registryResult;
            if (!$registryResult['passed']) {
                $results['is_eligible'] = false;
                $results['reasons'][] = $registryResult['reason'];
            }
        }

        // 5. Validar Certificación OSCE (si es requerida)
        // if ($jobProfile->requires_osce_certification) {
        //     $osceResult = $this->validateOsceCertification($application);
        //     $results['details']['osce_certification'] = $osceResult;
        //     if (!$osceResult['passed']) {
        //         $results['is_eligible'] = false;
        //         $results['reasons'][] = $osceResult['reason'];
        //     }
        // }

        // 6. Validar Licencia de Conducir (si es requerida)
        // if ($jobProfile->requires_driver_license) {
        //     $licenseResult = $this->validateDriverLicense($application);
        //     $results['details']['driver_license'] = $licenseResult;
        //     if (!$licenseResult['passed']) {
        //         $results['is_eligible'] = false;
        //         $results['reasons'][] = $licenseResult['reason'];
        //     }
        // }

        // 7. Validar Cursos Requeridos (si se especificaron)
        if (!empty($jobProfile->required_courses) && is_array($jobProfile->required_courses)) {
            $coursesResult = $this->validateRequiredCourses($application, $jobProfile);
            $results['details']['required_courses'] = $coursesResult;
            if (!$coursesResult['passed']) {
                $results['is_eligible'] = false;
                $results['reasons'][] = $coursesResult['reason'];
            }
        }

        // 8. Validar Conocimientos Técnicos (si se especificaron)
        // if (!empty($jobProfile->knowledge_areas) && is_array($jobProfile->knowledge_areas)) {
        //     $knowledgeResult = $this->validateTechnicalKnowledge($application, $jobProfile);
        //     $results['details']['technical_knowledge'] = $knowledgeResult;
        //     if (!$knowledgeResult['passed']) {
        //         $results['is_eligible'] = false;
        //         $results['reasons'][] = $knowledgeResult['reason'];
        //     }
        // }

        return $results;
    }

    /**
     * Aplicar evaluación automática y actualizar estado
     */
    public function applyAutoGrading(Application $application, string $checkedBy): Application
    {
        $evaluation = $this->evaluateEligibility($application);

        // Usar transacción para asegurar consistencia
        \DB::transaction(function () use ($application, $evaluation, $checkedBy) {
            // Actualizar estado de la aplicación
            $application->is_eligible = $evaluation['is_eligible'];
            $application->eligibility_checked_by = $checkedBy;
            $application->eligibility_checked_at = now();

            if ($evaluation['is_eligible']) {
                $application->status = ApplicationStatus::ELIGIBLE->value;
                $application->ineligibility_reason = null;
            } else {
                $application->status = ApplicationStatus::NOT_ELIGIBLE->value;
                $application->ineligibility_reason = implode("\n", $evaluation['reasons']);
            }

            $application->save();

            // Guardar evaluación detallada en la tabla de evaluaciones
            $application->evaluations()->create([
                'is_eligible' => $evaluation['is_eligible'],
                'ineligibility_reasons' => $evaluation['is_eligible']
                    ? null
                    : implode("\n", $evaluation['reasons']),
                'academics_evaluation' => $evaluation['details']['academics'] ?? null,
                'general_experience_evaluation' => $evaluation['details']['general_experience'] ?? null,
                'specific_experience_evaluation' => $evaluation['details']['specific_experience'] ?? null,
                'professional_registry_evaluation' => $evaluation['details']['professional_registry'] ?? null,
                'osce_certification_evaluation' => $evaluation['details']['osce_certification'] ?? null,
                'driver_license_evaluation' => $evaluation['details']['driver_license'] ?? null,
                'required_courses_evaluation' => $evaluation['details']['required_courses'] ?? null,
                'technical_knowledge_evaluation' => $evaluation['details']['technical_knowledge'] ?? null,
                'algorithm_version' => '1.0',
                'evaluated_by' => $checkedBy,
                'evaluated_at' => now(),
            ]);

            // Registrar en el historial
            $application->history()->create([
                'action' => 'eligibility_evaluated',
                'performed_by' => $checkedBy,
                'performed_at' => now(),
                'details' => [
                    'result' => $evaluation['is_eligible'] ? 'APTO' : 'NO_APTO',
                    'reasons' => $evaluation['reasons'],
                    'evaluation_details' => $evaluation['details'],
                    'algorithm_version' => '1.0',
                ],
            ]);
        });

        return $application->fresh();
    }

    /**
     * Validar formación académica
     *
     * Valida nivel educativo y colegiatura de forma determinista.
     * La validación de carrera se delega al LLM (Ollama) de forma asíncrona:
     * - Si ya existe un resultado IA completado → lo usa
     * - Si no existe → crea el job IA y marca como pendiente
     */
    private function validateAcademics(Application $application, $jobProfile): array
    {
        $academics = $application->academics;

        if ($academics->isEmpty()) {
            return [
                'passed' => false,
                'reason' => 'No se registró formación académica',
            ];
        }

        // 1. Validar nivel educativo requerido
        $requiredLevels = !empty($jobProfile->education_levels)
            ? $jobProfile->education_levels
            : [];

        $hasRequiredLevel = false;
        foreach ($requiredLevels as $requiredLevel) {
            if ($academics->contains(function ($academic) use ($requiredLevel) {
                return $this->compareEducationLevel($academic->degree_type, $requiredLevel) >= 0;
            })) {
                $hasRequiredLevel = true;
                break;
            }
        }

        if (!$hasRequiredLevel) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'No cumple con el nivel educativo requerido: %s',
                    implode(' o ', $requiredLevels)
                ),
            ];
        }

        // 2. Validar carrera profesional con IA (career_field del perfil)
        $careerResult = $this->validateCareerWithIa($application, $jobProfile);
        if ($careerResult !== null && !$careerResult['passed']) {
            return $careerResult;
        }

        // 3. Validar colegiatura si es requerida
        if ($jobProfile->colegiatura_required) {
            $hasColegiatura = $application->professionalRegistrations()
                ->where('registration_type', 'COLEGIATURA')
                ->whereRaw('(expiry_date IS NULL OR expiry_date >= CURDATE())')
                ->exists();

            if (!$hasColegiatura) {
                return [
                    'passed' => false,
                    'reason' => 'Requiere colegiatura profesional vigente',
                ];
            }
        }

        return [
            'passed' => $careerResult['passed'] ?? true,
            'reason' => $careerResult['reason'] ?? 'Cumple con la formación académica requerida',
            'ia_evaluation' => $careerResult['ia_evaluation'] ?? null,
            'pending_ia' => $careerResult['pending_ia'] ?? false,
        ];
    }

    /**
     * Validar carrera profesional usando evaluación IA (Ollama).
     *
     * Flujo:
     * 1. Si career_field está vacío → no hay requisito de carrera, retorna null (pasa)
     * 2. Si ya existe un job IA completado → usa ese resultado
     * 3. Si no existe → crea job IA y retorna "pendiente"
     */
    private function validateCareerWithIa(Application $application, $jobProfile): ?array
    {
        $careerField = $jobProfile->career_field;

        // Sin requisito de carrera → no aplica validación
        if (empty($careerField)) {
            return null;
        }

        // Buscar resultado IA completado para esta postulación
        $iaJob = \Modules\Application\Entities\IaJob::where('application_id', $application->id)
            ->where('status', 'completado')
            ->latest()
            ->first();

        if ($iaJob) {
            // Ya tenemos resultado del LLM → usarlo
            $passed = in_array($iaJob->resultado, ['cumple_exacto', 'cumple_equivalente', 'cumple_afin']);

            return [
                'passed' => $passed,
                'reason' => $passed
                    ? "Carrera validada por IA: {$iaJob->resultado} (score: {$iaJob->score}) - {$iaJob->justificacion}"
                    : "Carrera no cumple requisito (IA: {$iaJob->resultado}, score: {$iaJob->score}). Requiere: {$careerField}. {$iaJob->justificacion}",
                'ia_evaluation' => [
                    'job_id' => $iaJob->id,
                    'resultado' => $iaJob->resultado,
                    'score' => $iaJob->score,
                    'justificacion' => $iaJob->justificacion,
                ],
                'pending_ia' => false,
            ];
        }

        // No hay resultado IA → crear job para el agente
        try {
            $iaJobService = app(IaJobService::class);
            $newJob = $iaJobService->createCareerEvaluationJob($application);

            return [
                'passed' => false,
                'reason' => "Evaluación de carrera pendiente (IA). Requiere: {$careerField}",
                'ia_evaluation' => $newJob ? ['job_id' => $newJob->id, 'status' => 'pendiente'] : null,
                'pending_ia' => true,
            ];
        } catch (\Exception $e) {
            \Log::channel('ia')->error("Error creando job IA: {$e->getMessage()}", [
                'application_id' => $application->id,
            ]);

            return [
                'passed' => false,
                'reason' => "No se pudo evaluar carrera (error al crear job IA). Requiere: {$careerField}",
                'pending_ia' => true,
            ];
        }
    }

    /**
     * Validar experiencia general
     */
    private function validateGeneralExperience(Application $application, $jobProfile): array
    {
        $experiences = $application->experiences->map(fn($exp) => [
            'start_date' => $exp->start_date->toDateString(),
            'end_date' => $exp->end_date->toDateString(),
            'is_specific' => $exp->is_specific,
        ])->toArray();

        $result = $this->eligibilityCalculator->calculateGeneralExperience($experiences);

        // Obtener el valor requerido - usar el atributo raw para obtener el decimal de la BD
        $rawValue = $jobProfile->getAttributes()['general_experience_years'] ?? 0;
        $requiredYears = (float) $rawValue;

        // Formatear usando ExperienceDuration para consistencia
        $experienceObj = ExperienceDuration::fromDecimal($requiredYears);
        $requiredFormatted = $experienceObj->toHuman();

        if ($result['decimal_years'] < $requiredYears) {
            return [
                'passed' => false,
                'reason' => "Experiencia general insuficiente. Requerido: {$requiredFormatted} | Acreditado: {$result['formatted']}",
                'required' => $requiredFormatted,
                'achieved' => $result['formatted'],
            ];
        }

        return [
            'passed' => true,
            'reason' => "Cumple con la experiencia general requerida ({$result['formatted']})",
            'required' => $requiredFormatted,
            'achieved' => $result['formatted'],
        ];
    }

    /**
     * Validar experiencia específica
     */
    private function validateSpecificExperience(Application $application, $jobProfile): array
    {
        $experiences = $application->experiences->map(fn($exp) => [
            'start_date' => $exp->start_date->toDateString(),
            'end_date' => $exp->end_date->toDateString(),
            'is_specific' => $exp->is_specific,
        ])->toArray();

        $result = $this->eligibilityCalculator->calculateSpecificExperience($experiences);

        // Obtener el valor requerido - usar el atributo raw para obtener el decimal de la BD
        $rawValue = $jobProfile->getAttributes()['specific_experience_years'] ?? 0;
        $requiredYears = (float) $rawValue;

        // Formatear usando ExperienceDuration para consistencia
        $experienceObj = ExperienceDuration::fromDecimal($requiredYears);
        $requiredFormatted = $experienceObj->toHuman();

        if ($result['decimal_years'] < $requiredYears) {
            return [
                'passed' => false,
                'reason' => "Experiencia específica insuficiente. Requerido: {$requiredFormatted} | Acreditado: {$result['formatted']}",
                'required' => $requiredFormatted,
                'achieved' => $result['formatted'],
            ];
        }

        return [
            'passed' => true,
            'reason' => "Cumple con la experiencia específica requerida ({$result['formatted']})",
            'required' => $requiredFormatted,
            'achieved' => $result['formatted'],
        ];
    }

    /**
     * Validar colegiatura profesional
     */
    private function validateProfessionalRegistry(Application $application, $jobProfile): array
    {
        $registrations = $application->professionalRegistrations;

        $hasRegistry = $registrations->contains(function ($reg) {
            return $reg->registration_type === 'COLEGIATURA' && $reg->isValid();
        });

        if (!$hasRegistry) {
            return [
                'passed' => false,
                'reason' => 'No cuenta con colegiatura profesional vigente',
            ];
        }

        return [
            'passed' => true,
            'reason' => 'Cuenta con colegiatura profesional vigente',
        ];
    }

    /**
     * Validar certificación OSCE
     */
    private function validateOsceCertification(Application $application): array
    {
        $registrations = $application->professionalRegistrations;

        $hasOsce = $registrations->contains(function ($reg) {
            return $reg->registration_type === 'OSCE_CERTIFICATION' && $reg->isValid();
        });

        if (!$hasOsce) {
            return [
                'passed' => false,
                'reason' => 'No cuenta con certificación OSCE vigente',
            ];
        }

        return [
            'passed' => true,
            'reason' => 'Cuenta con certificación OSCE vigente',
        ];
    }

    /**
     * Validar licencia de conducir
     */
    private function validateDriverLicense(Application $application): array
    {
        $registrations = $application->professionalRegistrations;

        $hasLicense = $registrations->contains(function ($reg) {
            return $reg->registration_type === 'DRIVER_LICENSE' && $reg->isValid();
        });

        if (!$hasLicense) {
            return [
                'passed' => false,
                'reason' => 'No cuenta con licencia de conducir vigente',
            ];
        }

        return [
            'passed' => true,
            'reason' => 'Cuenta con licencia de conducir vigente',
        ];
    }

    /**
     * Validar cursos requeridos
     */
    private function validateRequiredCourses(Application $application, $jobProfile): array
    {
        $requiredCourses = $jobProfile->required_courses ?? [];
        $applicantTrainings = $application->trainings;

        if (empty($requiredCourses)) {
            return [
                'passed' => true,
                'reason' => 'No se requieren cursos específicos',
                'required' => [],
                'found' => [],
            ];
        }

        if ($applicantTrainings->isEmpty()) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'No registró capacitaciones. Se requieren cursos en: %s',
                    implode(', ', $requiredCourses)
                ),
                'required' => $requiredCourses,
                'found' => [],
            ];
        }

        // Normalizar nombres de cursos del postulante para búsqueda flexible
        $applicantCourseNames = $applicantTrainings->map(function ($training) {
            return strtolower(trim($training->course_name));
        })->toArray();

        // Verificar qué cursos requeridos coinciden
        $foundCourses = [];
        $missingCourses = [];

        foreach ($requiredCourses as $requiredCourse) {
            $requiredNormalized = strtolower(trim($requiredCourse));
            $found = false;

            // Buscar coincidencia parcial o completa
            foreach ($applicantCourseNames as $applicantCourse) {
                // Coincidencia si el curso requerido está contenido en el curso del postulante
                // o viceversa (búsqueda flexible)
                if (
                    str_contains($applicantCourse, $requiredNormalized) ||
                    str_contains($requiredNormalized, $applicantCourse)
                ) {
                    $found = true;
                    $foundCourses[] = $requiredCourse;
                    break;
                }
            }

            if (!$found) {
                $missingCourses[] = $requiredCourse;
            }
        }

        // Se requiere que al menos haya completado alguno de los cursos (OR logic)
        // Si quieres que todos sean requeridos (AND logic), cambia a: empty($missingCourses)
        $passed = !empty($foundCourses);

        return [
            'passed' => $passed,
            'reason' => $passed
                ? sprintf('Cumple con capacitación requerida: %s', implode(', ', $foundCourses))
                : sprintf('No cumple con capacitación requerida. Falta: %s', implode(', ', $missingCourses)),
            'required' => $requiredCourses,
            'found' => $foundCourses,
            'missing' => $missingCourses,
        ];
    }

    /**
     * Validar conocimientos técnicos requeridos
     */
    private function validateTechnicalKnowledge(Application $application, $jobProfile): array
    {
        $requiredKnowledge = $jobProfile->knowledge_areas ?? [];
        $applicantKnowledge = $application->knowledge;

        if (empty($requiredKnowledge)) {
            return [
                'passed' => true,
                'reason' => 'No se requieren conocimientos técnicos específicos',
                'required' => [],
                'found' => [],
            ];
        }

        if ($applicantKnowledge->isEmpty()) {
            return [
                'passed' => false,
                'reason' => sprintf(
                    'No registró conocimientos técnicos. Se requieren: %s',
                    implode(', ', $requiredKnowledge)
                ),
                'required' => $requiredKnowledge,
                'found' => [],
            ];
        }

        // Normalizar nombres de conocimientos del postulante
        $applicantKnowledgeNames = $applicantKnowledge->map(function ($knowledge) {
            return [
                'name' => strtolower(trim($knowledge->knowledge_name)),
                'level' => $knowledge->proficiency_level,
            ];
        })->toArray();

        // Verificar qué conocimientos requeridos coinciden
        $foundKnowledge = [];
        $missingKnowledge = [];

        foreach ($requiredKnowledge as $required) {
            // El required puede ser un string simple o un array con nivel
            if (is_array($required)) {
                $requiredName = strtolower(trim($required['name'] ?? $required['knowledge'] ?? ''));
                $requiredLevel = $required['level'] ?? null;
            } else {
                $requiredName = strtolower(trim($required));
                $requiredLevel = null;
            }

            $found = false;

            // Buscar coincidencia
            foreach ($applicantKnowledgeNames as $applicantKnow) {
                if (
                    str_contains($applicantKnow['name'], $requiredName) ||
                    str_contains($requiredName, $applicantKnow['name'])
                ) {
                    // Si se especifica nivel requerido, validar nivel de dominio
                    if ($requiredLevel) {
                        if ($this->compareProficiencyLevel($applicantKnow['level'], $requiredLevel) >= 0) {
                            $found = true;
                            $foundKnowledge[] = is_array($required) ? ($required['name'] ?? $required) : $required;
                            break;
                        }
                    } else {
                        $found = true;
                        $foundKnowledge[] = is_array($required) ? ($required['name'] ?? $required) : $required;
                        break;
                    }
                }
            }

            if (!$found) {
                $missingKnowledge[] = is_array($required) ? ($required['name'] ?? $required) : $required;
            }
        }

        // Se requiere que al menos tenga uno de los conocimientos (OR logic)
        // Si quieres que todos sean requeridos (AND logic), cambia a: empty($missingKnowledge)
        $passed = !empty($foundKnowledge);

        return [
            'passed' => $passed,
            'reason' => $passed
                ? sprintf('Cumple con conocimientos técnicos: %s', implode(', ', $foundKnowledge))
                : sprintf('No cumple con conocimientos técnicos requeridos. Falta: %s', implode(', ', $missingKnowledge)),
            'required' => $requiredKnowledge,
            'found' => $foundKnowledge,
            'missing' => $missingKnowledge,
        ];
    }

    /**
     * Comparar niveles de dominio de conocimientos
     * Retorna: -1 (menor), 0 (igual), 1 (mayor)
     */
    private function compareProficiencyLevel(?string $applicantLevel, string $requiredLevel): int
    {
        $levels = [
            'BASICO' => 1,
            'INTERMEDIO' => 2,
            'AVANZADO' => 3,
        ];

        $applicantValue = $levels[strtoupper($applicantLevel ?? '')] ?? 0;
        $requiredValue = $levels[strtoupper($requiredLevel)] ?? 0;

        return $applicantValue <=> $requiredValue;
    }

    /**
     * Comparar niveles educativos
     * Retorna: -1 (menor), 0 (igual), 1 (mayor)
     */
    private function compareEducationLevel(string $applicantLevel, string $requiredLevel): int
    {
        $levels = [
            'SECUNDARIA' => 1,
            'secundaria' => 1,
            'TECNICO' => 2,
            'tecnico' => 2,
            'titulo_tecnico' => 2,
            'TITULO_TECNICO' => 2,
            'BACHILLER' => 3,
            'bachiller' => 3,
            'TITULO' => 4,
            'titulo' => 4,
            'titulo_profesional' => 4,
            'TITULO_PROFESIONAL' => 4,
            'MAESTRIA' => 5,
            'maestria' => 5,
            'DOCTORADO' => 6,
            'doctorado' => 6,
        ];

        $applicantValue = $levels[$applicantLevel] ?? 0;
        $requiredValue = $levels[$requiredLevel] ?? 0;

        return $applicantValue <=> $requiredValue;
    }

    /**
     * Aplicar evaluación automática integrando con el módulo de Evaluation
     *
     * Este método:
     * 1. Ejecuta la evaluación de elegibilidad (evaluateEligibility)
     * 2. Crea una Evaluation en el módulo Evaluation con la Fase 4
     * 3. Guarda cada criterio como EvaluationDetail
     * 4. Actualiza la Application con el resultado
     * 5. Mantiene compatibilidad guardando también en ApplicationEvaluation
     *
     * @param Application $application Postulación a evaluar
     * @param string $evaluatedBy UUID del usuario que ejecuta la evaluación
     * @return \Modules\Evaluation\Entities\Evaluation
     */
    public function applyAutoGradingWithEvaluationModule(Application $application, string $evaluatedBy): \Modules\Evaluation\Entities\Evaluation
    {
        \DB::beginTransaction();
        try {
            // 1. Ejecutar evaluación de elegibilidad (lógica existente)
            $result = $this->evaluateEligibility($application);

            // 2. Obtener la Fase 4 - Publicación de postulantes APTOS
            $phase4 = \Modules\JobPosting\Entities\ProcessPhase::where('code', 'PHASE_04_ELIGIBLE_PUB')->firstOrFail();

            // 3. Obtener job_posting de la vacancy (usamos el UUID)
            $jobPosting = $application->jobProfile->jobPosting;

            // 4. Crear Evaluation en el módulo de Evaluation
            $evaluationService = app(\Modules\Evaluation\Services\EvaluationService::class);

            $evaluation = $evaluationService->createEvaluation([
                'application_id' => $application->id, // UUID de la application
                'evaluator_id' => $evaluatedBy, // UUID del evaluador
                'phase_id' => $phase4->id, // process_phases no tiene uuid, usar id
                'job_posting_id' => $jobPosting->id, // UUID del job posting
                'is_anonymous' => false,
                'is_collaborative' => false,
                'general_comments' => $result['is_eligible']
                    ? 'El postulante cumple con todos los requisitos mínimos de elegibilidad evaluados automáticamente por el sistema.'
                    : 'El postulante NO cumple con los siguientes requisitos: ' . implode('; ', $result['reasons']),
                'internal_notes' => 'Evaluación automática ejecutada por AutoGraderService v1.0',
            ]);

            // 5. Mapeo de criterios de elegibilidad a códigos de EvaluationCriterion
            $criteriaMapping = [
                'ELIGIBILITY_ACADEMIC' => $result['details']['academics'] ?? null,
                'ELIGIBILITY_GENERAL_EXPERIENCE' => $result['details']['general_experience'] ?? null,
                'ELIGIBILITY_SPECIFIC_EXPERIENCE' => $result['details']['specific_experience'] ?? null,
                'ELIGIBILITY_PROFESSIONAL_REGISTRY' => $result['details']['professional_registry'] ?? null,
                'ELIGIBILITY_OSCE_CERTIFICATION' => $result['details']['osce_certification'] ?? null,
                'ELIGIBILITY_DRIVER_LICENSE' => $result['details']['driver_license'] ?? null,
                'ELIGIBILITY_REQUIRED_COURSES' => $result['details']['required_courses'] ?? null,
                'ELIGIBILITY_TECHNICAL_KNOWLEDGE' => $result['details']['technical_knowledge'] ?? null,
            ];

            // 6. Obtener TODOS los criterios activos de la fase 4 para garantizar completitud
            $allCriteria = \Modules\Evaluation\Entities\EvaluationCriterion::active()
                ->where('phase_id', $phase4->id)
                ->where(function ($q) use ($jobPosting) {
                    $q->whereNull('job_posting_id')
                      ->orWhere('job_posting_id', $jobPosting->id);
                })
                ->get();

            // 7. Guardar detalles de cada criterio (evaluado o no aplicable)
            foreach ($allCriteria as $criterion) {
                $detail = $criteriaMapping[$criterion->code] ?? null;

                if ($detail !== null) {
                    // Criterio fue evaluado - guardar resultado
                    $evaluationService->saveEvaluationDetail($evaluation, [
                        'criterion_id' => $criterion->id,
                        'score' => $detail['passed'] ? 1 : 0, // 0 = No cumple, 1 = Cumple
                        'comments' => $detail['reason'] ?? 'Criterio evaluado automáticamente',
                        'evidence' => null,
                        'metadata' => [
                            'required' => $detail['required'] ?? null,
                            'achieved' => $detail['achieved'] ?? null,
                            'full_detail' => $detail,
                        ],
                    ]);
                } else {
                    // Criterio no aplica para este perfil - marcar como "No aplica" con score máximo
                    $evaluationService->saveEvaluationDetail($evaluation, [
                        'criterion_id' => $criterion->id,
                        'score' => 1, // Cumple por defecto (no es requisito para este perfil)
                        'comments' => 'No aplica para este perfil de puesto',
                        'evidence' => null,
                        'metadata' => [
                            'not_applicable' => true,
                            'reason' => 'El perfil del puesto no requiere este criterio',
                        ],
                    ]);
                }
            }

            // 8. Enviar evaluación (marcar como SUBMITTED)
            $evaluationService->submitEvaluation($evaluation);

            // 9. Actualizar Application (mantener compatibilidad)
            $application->update([
                'is_eligible' => $result['is_eligible'],
                'status' => $result['is_eligible']
                    ? ApplicationStatus::ELIGIBLE
                    : ApplicationStatus::NOT_ELIGIBLE,
                'ineligibility_reason' => implode("\n", $result['reasons'] ?? []),
                'eligibility_checked_at' => now(),
                'eligibility_checked_by' => $evaluatedBy,
            ]);

            // 10. También guardar en ApplicationEvaluation (mantener compatibilidad con sistema existente)
            \Modules\Application\Entities\ApplicationEvaluation::create([
                'application_id' => $application->id,
                'is_eligible' => $result['is_eligible'],
                'ineligibility_reasons' => implode("\n", $result['reasons'] ?? []),
                'academics_evaluation' => $result['details']['academics'] ?? null,
                'general_experience_evaluation' => $result['details']['general_experience'] ?? null,
                'specific_experience_evaluation' => $result['details']['specific_experience'] ?? null,
                'professional_registry_evaluation' => $result['details']['professional_registry'] ?? null,
                'osce_certification_evaluation' => $result['details']['osce_certification'] ?? null,
                'driver_license_evaluation' => $result['details']['driver_license'] ?? null,
                'required_courses_evaluation' => $result['details']['required_courses'] ?? null,
                'technical_knowledge_evaluation' => $result['details']['technical_knowledge'] ?? null,
                'algorithm_version' => '1.0',
                'evaluated_by' => $evaluatedBy,
                'evaluated_at' => now(),
            ]);

            // 10. Registrar en historial de application
            \Modules\Application\Entities\ApplicationHistory::create([
                'application_id' => $application->id,
                'event_type' => 'EVALUATED',
                'description' => $result['is_eligible']
                    ? 'Postulación marcada como APTO por evaluación automática'
                    : 'Postulación marcada como NO APTO por evaluación automática: ' . implode(', ', $result['reasons']),
                'performed_by' => $evaluatedBy,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'evaluation_id' => $evaluation->id,
                    'phase_id' => $phase4->id,
                    'auto_grading' => true,
                    'is_eligible' => $result['is_eligible'],
                    'reasons' => $result['reasons'] ?? [],
                ],
                'performed_at' => now(),
            ]);

            \DB::commit();

            // Refrescar evaluation para obtener scores actualizados
            $evaluation->refresh();

            return $evaluation;

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validar carrera afín usando el servicio NLP
     *
     * Este método se llama cuando el postulante declaró una "carrera afín"
     * que no está en el catálogo de carreras mapeadas. Usa procesamiento
     * de lenguaje natural para determinar si es similar a las requeridas.
     *
     * @param \Illuminate\Support\Collection $relatedCareers Carreras afines del postulante
     * @param array $requiredCareerNames Nombres de carreras requeridas por el perfil
     * @return array|null Resultado de validación si hay match o requiere revisión, null si debe continuar validación normal
     */
    private function validateRelatedCareerWithNlp(
        \Illuminate\Support\Collection $relatedCareers,
        array $requiredCareerNames
    ): ?array {
        if (empty($requiredCareerNames)) {
            return null;
        }

        foreach ($relatedCareers as $academic) {
            $candidateCareer = $academic->related_career_name;

            if (empty($candidateCareer)) {
                continue;
            }

            try {
                $matchResult = $this->careerMatcher->matchRelatedCareer(
                    $candidateCareer,
                    $requiredCareerNames
                );

                // Si hay match por NLP, la carrera afín es válida
                if ($matchResult['is_match']) {
                    return [
                        'passed' => true,
                        'reason' => sprintf(
                            'Carrera afín "%s" validada por similitud con "%s" (score: %.0f%%)',
                            $candidateCareer,
                            $matchResult['matched_career'],
                            $matchResult['score'] * 100
                        ),
                        'validation_type' => 'nlp',
                        'nlp_result' => [
                            'candidate_career' => $candidateCareer,
                            'matched_career' => $matchResult['matched_career'],
                            'match_type' => $matchResult['match_type'],
                            'score' => $matchResult['score'],
                            'threshold' => $matchResult['threshold_used'] ?? 0.75,
                        ],
                    ];
                }

                // Si el servicio NLP no está disponible, marcar para revisión manual
                if ($matchResult['requires_manual_review'] ?? false) {
                    return [
                        'passed' => false,
                        'reason' => sprintf(
                            'Carrera afín "%s" requiere revisión manual (servicio NLP no disponible)',
                            $candidateCareer
                        ),
                        'requires_manual_review' => true,
                        'validation_type' => 'pending_manual',
                        'nlp_error' => $matchResult['reason'] ?? 'Servicio no disponible',
                    ];
                }

                // El servicio NLP funcionó pero no hubo match
                // Registrar el intento para trazabilidad
                \Log::info('NLP career match failed', [
                    'candidate' => $candidateCareer,
                    'required' => $requiredCareerNames,
                    'score' => $matchResult['score'],
                    'all_scores' => $matchResult['all_scores'] ?? null,
                ]);

            } catch (\Exception $e) {
                \Log::error('Error calling NLP career matcher', [
                    'candidate' => $candidateCareer,
                    'error' => $e->getMessage(),
                ]);

                // En caso de error, marcar para revisión manual
                return [
                    'passed' => false,
                    'reason' => sprintf(
                        'Carrera afín "%s" requiere revisión manual (error en validación NLP)',
                        $candidateCareer
                    ),
                    'requires_manual_review' => true,
                    'validation_type' => 'error',
                    'nlp_error' => $e->getMessage(),
                ];
            }
        }

        // No hubo match NLP para ninguna carrera afín
        return null;
    }
}
