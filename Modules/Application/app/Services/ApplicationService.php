<?php

namespace Modules\Application\Services;

use Modules\Application\Entities\Application;
use Modules\Application\DTOs\ApplicationDTO;
use Modules\Application\Repositories\Contracts\ApplicationRepositoryInterface;
use Modules\Application\Events\ApplicationSubmitted;
use Modules\Application\Events\ApplicationUpdated;
use Modules\Application\Events\ApplicationEvaluated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio principal para gestión de postulaciones
 *
 * Responsabilidades:
 * - Crear y actualizar postulaciones
 * - Calcular elegibilidad automática
 * - Gestionar ciclo de vida de la postulación
 * - Coordinar con otros servicios
 */
class ApplicationService
{
    public function __construct(
        protected ApplicationRepositoryInterface $repository,
        protected EligibilityCalculatorService $eligibilityCalculator,
        protected AutoGraderService $autoGrader
    ) {}

    /**
     * Crear nueva postulación
     */
    public function create(ApplicationDTO $dto): Application
    {
        return DB::transaction(function () use ($dto) {
            // 1. Verificar que no haya postulado antes a este perfil
            if ($this->repository->hasApplied($dto->applicantId, $dto->jobProfileId)) {
                throw new \Exception('Ya existe una postulación activa para este perfil');
            }

            // 2. Generar código único
            $code = Application::generateCode('CONV-2025');

            // 3. Crear la postulación principal
            $application = $this->repository->create([
                'code' => $code,
                'job_profile_id' => $dto->jobProfileId,        // ← ACTUALIZADO
                'assigned_vacancy_id' => null,                  // ← NUEVO: sin vacante asignada inicialmente
                'applicant_id' => $dto->applicantId,
                'status' => 'PRESENTADA',
                'application_date' => now(),
                'terms_accepted' => $dto->termsAccepted,
                'full_name' => $dto->personalData->fullName,
                'dni' => $dto->personalData->dni,
                'birth_date' => $dto->personalData->birthDate,
                'address' => $dto->personalData->address,
                'phone' => $dto->personalData->phone,
                'mobile_phone' => $dto->personalData->mobilePhone,
                'email' => $dto->personalData->email,
                'ip_address' => $dto->ipAddress,
                'notes' => $dto->notes,
            ]);

            // 4. Crear registros relacionados
            $this->createAcademics($application, $dto->academics);
            $this->createExperiences($application, $dto->experiences);
            $this->createTrainings($application, $dto->trainings);
            $this->createSpecialConditions($application, $dto->specialConditions);
            $this->createProfessionalRegistrations($application, $dto->professionalRegistrations);
            $this->createKnowledge($application, $dto->knowledge);

            // 5. Calcular bonificación por condiciones especiales
            $this->calculateSpecialConditionBonus($application);

            // 6. Disparar evento
            event(new ApplicationSubmitted($application));

            // 7. Retornar postulación con relaciones cargadas
            return $this->repository->find($application->id);
        });
    }

    /**
     * Actualizar postulación existente
     */
    public function update(Application $application, ApplicationDTO $dto): Application
    {
        return DB::transaction(function () use ($application, $dto) {
            // Verificar que esté en estado editable
            if (!$application->isEditable()) {
                throw new \Exception('La postulación no puede ser modificada en su estado actual');
            }

            // Actualizar datos principales
            $this->repository->update($application, [
                'full_name' => $dto->personalData->fullName,
                'address' => $dto->personalData->address,
                'phone' => $dto->personalData->phone,
                'mobile_phone' => $dto->personalData->mobilePhone,
                'email' => $dto->personalData->email,
                'notes' => $dto->notes,
            ]);

            // Actualizar relaciones (eliminar y recrear)
            $application->academics()->delete();
            $application->experiences()->delete();
            $application->trainings()->delete();
            $application->specialConditions()->delete();
            $application->professionalRegistrations()->delete();
            $application->knowledge()->delete();

            $this->createAcademics($application, $dto->academics);
            $this->createExperiences($application, $dto->experiences);
            $this->createTrainings($application, $dto->trainings);
            $this->createSpecialConditions($application, $dto->specialConditions);
            $this->createProfessionalRegistrations($application, $dto->professionalRegistrations);
            $this->createKnowledge($application, $dto->knowledge);

            // Recalcular bonificación
            $this->calculateSpecialConditionBonus($application);

            // Disparar evento
            event(new ApplicationUpdated($application));

            return $this->repository->find($application->id);
        });
    }

    /**
     * Evaluar elegibilidad automáticamente
     */
    public function evaluateEligibility(Application $application, string $checkedBy): Application
    {
        $application = $this->autoGrader->applyAutoGrading($application, $checkedBy);

        event(new ApplicationEvaluated($application));

        return $application;
    }

    /**
     * Calcular experiencia de una postulación
     */
    public function calculateExperience(Application $application): array
    {
        $experiences = $application->experiences->map(fn($exp) => [
            'start_date' => $exp->start_date->toDateString(),
            'end_date' => $exp->end_date->toDateString(),
            'is_specific' => $exp->is_specific,
            'is_public_sector' => $exp->is_public_sector,
        ])->toArray();

        return [
            'general' => $this->eligibilityCalculator->calculateGeneralExperience($experiences),
            'specific' => $this->eligibilityCalculator->calculateSpecificExperience($experiences),
            'public_sector' => $this->eligibilityCalculator->calculatePublicSectorExperience($experiences),
            'overlaps' => $this->eligibilityCalculator->detectOverlaps($experiences),
        ];
    }

    /**
     * Retirar postulación (cambiar a DESISTIDA)
     */
    public function withdraw(Application $application, string $reason = null): Application
    {
        if (!in_array($application->status, [
            \Modules\Application\Enums\ApplicationStatus::SUBMITTED,
            \Modules\Application\Enums\ApplicationStatus::IN_REVIEW,
            \Modules\Application\Enums\ApplicationStatus::ELIGIBLE
        ])) {
            throw new \Exception('No se puede desistir de la postulación en su estado actual');
        }

        $this->repository->update($application, [
            'status' => \Modules\Application\Enums\ApplicationStatus::WITHDRAWN,
            'notes' => ($application->notes ? $application->notes . "\n\n" : '') . "Desistimiento: " . ($reason ?? 'Sin especificar'),
        ]);

        return $application;
    }

    /**
     * Crear formación académica
     */
    private function createAcademics(Application $application, array $academics): void
    {
        foreach ($academics as $academic) {
            $application->academics()->create($academic->toArray());
        }
    }

    /**
     * Crear experiencias laborales
     */
    private function createExperiences(Application $application, array $experiences): void
    {
        foreach ($experiences as $experience) {
            $application->experiences()->create($experience->toArray());
        }
    }

    /**
     * Crear capacitaciones
     */
    private function createTrainings(Application $application, array $trainings): void
    {
        foreach ($trainings as $training) {
            $application->trainings()->create($training->toArray());
        }
    }

    /**
     * Crear condiciones especiales
     */
    private function createSpecialConditions(Application $application, array $specialConditions): void
    {
        foreach ($specialConditions as $condition) {
            $application->specialConditions()->create($condition->toArray());
        }
    }

    /**
     * Crear registros profesionales
     */
    private function createProfessionalRegistrations(Application $application, array $registrations): void
    {
        foreach ($registrations as $registration) {
            $application->professionalRegistrations()->create($registration->toArray());
        }
    }

    /**
     * Crear conocimientos
     */
    private function createKnowledge(Application $application, array $knowledge): void
    {
        foreach ($knowledge as $item) {
            $application->knowledge()->create($item->toArray());
        }
    }

    /**
     * Calcular bonificación por condiciones especiales
     *
     * Si hay múltiples condiciones, se aplica la mayor
     */
    private function calculateSpecialConditionBonus(Application $application): void
    {
        $maxBonus = 0;

        foreach ($application->specialConditions as $condition) {
            if ($condition->is_verified && $condition->isValid()) {
                $maxBonus = max($maxBonus, $condition->bonus_percentage);
            }
        }

        $application->special_condition_bonus = $maxBonus;
        $application->save();
    }

    /**
     * Obtener estadísticas de una postulación
     */
    public function getStatistics(Application $application): array
    {
        $experienceData = $this->calculateExperience($application);

        return [
            'academics_count' => $application->academics->count(),
            'experiences_count' => $application->experiences->count(),
            'trainings_count' => $application->trainings->count(),
            'total_training_hours' => $application->trainings->sum('academic_hours'),
            'special_conditions_count' => $application->specialConditions->count(),
            'knowledge_count' => $application->knowledge->count(),
            'general_experience' => $experienceData['general'],
            'specific_experience' => $experienceData['specific'],
            'public_sector_experience' => $experienceData['public_sector'],
            'overlaps_detected' => count($experienceData['overlaps']),
            'special_condition_bonus' => $application->special_condition_bonus,
        ];
    }
    /**
     * Obtener las postulaciones de un usuario por id
     */
    public function getUserApplications($userId, $status = null, $search = null)
    {
        $query = Application::where('applicant_id', $userId)
            ->with(['vacancy.jobProfile.jobPosting']);

        // Filtrar por estado si se proporciona
        if ($status) {
            $query->where('status', $status);
        }

        // Buscar por código o nombre si se proporciona
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('full_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('vacancy.jobProfile', function($query) use ($search) {
                      $query->where('profile_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        return $query->latest()->paginate(10);
    }

    /**
     * Obtener el conteo de postulaciones por estado de un usuario
     */
    public function getUserApplicationStatusCounts(string $userId): array
    {
        $applications = Application::where('applicant_id', $userId)->get();

        return [
            'all' => $applications->count(),
            'draft' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::DRAFT)->count(),
            'submitted' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::SUBMITTED)->count(),
            'in_review' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::IN_REVIEW)->count(),
            'eligible' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)->count(),
            'not_eligible' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::NOT_ELIGIBLE)->count(),
            'in_evaluation' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::IN_EVALUATION)->count(),
            'amendment_required' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::AMENDMENT_REQUIRED)->count(),
            'approved' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::APPROVED)->count(),
            'rejected' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::REJECTED)->count(),
            'withdrawn' => $applications->where('status', \Modules\Application\Enums\ApplicationStatus::WITHDRAWN)->count(),
        ];
    }

    /**
     * Obtener una postulación por ID
     */
    public function getApplicationById($applicationId)
    {
        return Application::find($applicationId);
    }

    /**
     * Desistir de una postulación
     */
    public function withdrawApplication(string $applicationId, string $userId, string $reason = null): Application
    {
        $application = $this->getApplicationById($applicationId);

        if (!$application) {
            throw new \Exception('Postulación no encontrada');
        }

        if ($application->applicant_id !== $userId) {
            throw new \Exception('No tienes permiso para desistir de esta postulación');
        }

        return $this->withdraw($application, $reason);
    }

    public function submitApplication(string $id)
    {
        return DB::transaction(function () use ($id) {
            $application = $this->getApplicationById($id);

            if (!$application) {
                throw new \Exception("Postulacion no encontrada por ID: {$id}");
            }
            $currentStatus = $application->status;

            if (!$currentStatus->canTransitionTo(\Modules\Application\Enums\ApplicationStatus::SUBMITTED)) {
                throw new \Exception(
                    "No se puede enviar la postulación. Estado actual: {$currentStatus->label()}"
                );
            }

            $application->status = \Modules\Application\Enums\ApplicationStatus::SUBMITTED;
            $application->save();

            // Aquí podrías disparar eventos si es necesario
            // event(new ApplicationSubmitted($application));

            return $application;
        });
    }
}
