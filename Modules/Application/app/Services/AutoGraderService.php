<?php

namespace Modules\Application\Services;

use Modules\Application\Entities\Application;
use Modules\Application\Enums\ApplicationStatus;

/**
 * Servicio para evaluación automática de elegibilidad
 *
 * Compara los datos de la postulación contra los requisitos del JobProfile
 * y determina automáticamente si es APTO o NO_APTO
 */
class AutoGraderService
{
    public function __construct(
        private EligibilityCalculatorService $eligibilityCalculator
    ) {}

    /**
     * Evaluar elegibilidad de una postulación
     *
     * @param Application $application
     * @return array ['is_eligible' => bool, 'reasons' => array, 'details' => array]
     */
    public function evaluateEligibility(Application $application): array
    {
        $vacancy = $application->vacancy;
        $jobProfile = $vacancy->jobProfileRequest;

        $results = [
            'is_eligible' => true,
            'reasons' => [],
            'details' => [],
        ];

        // 1. Validar Formación Académica
        $academicResult = $this->validateAcademics($application, $jobProfile);
        $results['details']['academics'] = $academicResult;
        if (!$academicResult['passed']) {
            $results['is_eligible'] = false;
            $results['reasons'][] = $academicResult['reason'];
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
        if ($jobProfile->requires_professional_registry) {
            $registryResult = $this->validateProfessionalRegistry($application, $jobProfile);
            $results['details']['professional_registry'] = $registryResult;
            if (!$registryResult['passed']) {
                $results['is_eligible'] = false;
                $results['reasons'][] = $registryResult['reason'];
            }
        }

        // 5. Validar Certificación OSCE (si es requerida)
        if ($jobProfile->requires_osce_certification) {
            $osceResult = $this->validateOsceCertification($application);
            $results['details']['osce_certification'] = $osceResult;
            if (!$osceResult['passed']) {
                $results['is_eligible'] = false;
                $results['reasons'][] = $osceResult['reason'];
            }
        }

        // 6. Validar Licencia de Conducir (si es requerida)
        if ($jobProfile->requires_driver_license) {
            $licenseResult = $this->validateDriverLicense($application);
            $results['details']['driver_license'] = $licenseResult;
            if (!$licenseResult['passed']) {
                $results['is_eligible'] = false;
                $results['reasons'][] = $licenseResult['reason'];
            }
        }

        return $results;
    }

    /**
     * Aplicar evaluación automática y actualizar estado
     */
    public function applyAutoGrading(Application $application, string $checkedBy): Application
    {
        $evaluation = $this->evaluateEligibility($application);

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

        return $application;
    }

    /**
     * Validar formación académica
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

        // Verificar nivel educativo requerido
        $requiredLevel = $jobProfile->education_level;
        $hasRequiredLevel = $academics->contains(function ($academic) use ($requiredLevel) {
            return $this->compareEducationLevel($academic->degree_type, $requiredLevel) >= 0;
        });

        if (!$hasRequiredLevel) {
            return [
                'passed' => false,
                'reason' => "No cumple con el nivel educativo requerido: {$requiredLevel}",
            ];
        }

        // Verificar carrera específica (si es requerida)
        if ($jobProfile->career_field) {
            $hasCareer = $academics->contains(function ($academic) use ($jobProfile) {
                return stripos($academic->career_field, $jobProfile->career_field) !== false;
            });

            if (!$hasCareer) {
                return [
                    'passed' => false,
                    'reason' => "No cumple con la carrera requerida: {$jobProfile->career_field}",
                ];
            }
        }

        return [
            'passed' => true,
            'reason' => 'Cumple con la formación académica requerida',
        ];
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
        $requiredYears = $jobProfile->general_experience_years ?? 0;

        if ($result['decimal_years'] < $requiredYears) {
            return [
                'passed' => false,
                'reason' => "Experiencia general insuficiente. Requerido: {$requiredYears} años, Acreditado: {$result['formatted']}",
                'required' => $requiredYears,
                'achieved' => $result['decimal_years'],
            ];
        }

        return [
            'passed' => true,
            'reason' => "Cumple con la experiencia general requerida",
            'required' => $requiredYears,
            'achieved' => $result['decimal_years'],
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
        $requiredYears = $jobProfile->specific_experience_years ?? 0;

        if ($result['decimal_years'] < $requiredYears) {
            return [
                'passed' => false,
                'reason' => "Experiencia específica insuficiente. Requerido: {$requiredYears} años, Acreditado: {$result['formatted']}",
                'required' => $requiredYears,
                'achieved' => $result['decimal_years'],
            ];
        }

        return [
            'passed' => true,
            'reason' => "Cumple con la experiencia específica requerida",
            'required' => $requiredYears,
            'achieved' => $result['decimal_years'],
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
     * Comparar niveles educativos
     * Retorna: -1 (menor), 0 (igual), 1 (mayor)
     */
    private function compareEducationLevel(string $applicantLevel, string $requiredLevel): int
    {
        $levels = [
            'SECUNDARIA' => 1,
            'TECNICO' => 2,
            'BACHILLER' => 3,
            'TITULO' => 4,
            'MAESTRIA' => 5,
            'DOCTORADO' => 6,
        ];

        $applicantValue = $levels[$applicantLevel] ?? 0;
        $requiredValue = $levels[$requiredLevel] ?? 0;

        return $applicantValue <=> $requiredValue;
    }
}
