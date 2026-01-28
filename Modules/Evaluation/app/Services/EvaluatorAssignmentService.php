<?php

namespace Modules\Evaluation\Services;

use Modules\Evaluation\Entities\EvaluatorAssignment;
use Modules\Jury\Services\{JuryAssignmentService, ConflictDetectionService};
use Modules\Jury\Entities\{JuryAssignment, JuryConflict};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para gestionar asignaciones de evaluadores a postulaciones
 *
 * Según diseño optimizado:
 * - Usa user_id directamente (sin JuryMember)
 * - Verifica que el usuario esté asignado como jurado en JuryAssignment
 * - Detecta conflictos antes de asignar
 * - Distribuye carga equitativamente
 */
class EvaluatorAssignmentService
{
    public function __construct(
        protected JuryAssignmentService $juryAssignmentService,
        protected ConflictDetectionService $conflictService
    ) {}

    /**
     * Asignar evaluador a postulación
     *
     * @param array $data ['user_id', 'application_id', 'phase_id', 'assignment_type'?, 'deadline_at'?]
     */
    public function assignEvaluator(array $data): EvaluatorAssignment
{
    Log::info("Iniciando asignación de evaluador", [
        'data' => $data,
    ]);

    try {
        // 0. Buscar la aplicación
        $application = \Modules\Application\Entities\Application::with('jobProfile')->findOrFail($data['application_id']);
        Log::info("Aplicación encontrada", [
            'applicationId' => $application->id,
            'jobPostingId' => $application->job_profile_id,
        ]);

        // 1. Verificar que el usuario es un jurado asignado a la convocatoria
        $juryAssignment = JuryAssignment::where('user_id', $data['user_id'])
            ->where('job_posting_id', $application->jobProfile->job_posting_id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$juryAssignment) {
            Log::warning("El usuario no es jurado en esta convocatoria", [
                'userId' => $data['user_id'],
                'jobPostingId' => $application->job_profile_id,
            ]);
            throw new \Exception('El usuario no está asignado como jurado en esta convocatoria');
        }

        Log::info("Jurado verificado", [
            'userId' => $data['user_id'],
            'juryAssignmentId' => $juryAssignment->id,
        ]);

        if (!$juryAssignment->canEvaluate()) {
            Log::warning("El jurado no puede evaluar en este momento", [
                'userId' => $data['user_id'],
                'juryAssignmentId' => $juryAssignment->id,
            ]);
            throw new \Exception('El jurado no puede evaluar en este momento');
        }

        // 2. Verificar todos los conflictos (manuales + automáticos)
        $conflictCheck = $this->conflictService->checkAllAutomaticConflicts($data['user_id'], $data['application_id']);
        if ($conflictCheck['has_conflict']) {
            $reasons = implode('. ', $conflictCheck['reasons']);
            Log::warning("Conflicto de interés detectado", [
                'userId' => $data['user_id'],
                'applicationId' => $data['application_id'],
                'reasons' => $conflictCheck['reasons'],
            ]);
            throw new \Exception("El jurado tiene conflictos de interés con esta postulación: {$reasons}");
        }

        // 3. Verificar si ya existe asignación para esta combinación
        $existing = EvaluatorAssignment::where('user_id', $data['user_id'])
            ->where('application_id', $data['application_id'])
            ->where('phase_id', $data['phase_id'])
            ->first();

        if ($existing) {
            Log::warning("Evaluador ya asignado a esta postulación y fase", [
                'userId' => $data['user_id'],
                'applicationId' => $data['application_id'],
                'phaseId' => $data['phase_id'],
            ]);
            throw new \Exception('El evaluador ya está asignado a esta postulación en esta fase');
        }

        // 4. Crear asignación de evaluador
        $evaluatorAssignment = EvaluatorAssignment::create([
            'user_id' => $data['user_id'],
            'application_id' => $data['application_id'],
            'phase_id' => $data['phase_id'],
            'job_posting_id' => $application->jobProfile->job_posting_id,
            'assignment_type' => $data['assignment_type'] ?? 'MANUAL',
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'deadline_at' => $data['deadline_at'] ?? null,
        ]);

        Log::info("Asignación de evaluador creada exitosamente", [
            'evaluatorAssignmentId' => $evaluatorAssignment->id,
            'userId' => $evaluatorAssignment->user_id,
            'applicationId' => $evaluatorAssignment->application_id,
            'phaseId' => $evaluatorAssignment->phase_id,
            'jobPostingId' => $evaluatorAssignment->job_posting_id,
        ]);

        return $evaluatorAssignment->fresh(['user', 'application', 'phase']);

    } catch (\Exception $e) {
        Log::error("Error al asignar evaluador", [
            'data' => $data,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        throw $e;
    }
}

    /**
     * Obtener evaluadores disponibles para una postulación
     *
     * Retorna jurados que:
     * - Están asignados activamente a la convocatoria
     * - No tienen conflictos con la postulación
     * - No están sobrecargados
     */
    public function getAvailableEvaluators(
        string $applicationId,
        ?string $phaseId = null
    ): Collection {
        $application = \Modules\Application\Entities\Application::with('jobProfile')->findOrFail($applicationId);

        // Obtener jurados disponibles desde JuryAssignmentService
        $availableAssignments = $this->juryAssignmentService->getAvailableEvaluators(
            $application->jobProfile->job_posting_id,
            $applicationId
        );

        // Calcular carga actual
        $userIds = $availableAssignments->pluck('user_id')->toArray();
        $workload = $this->calculateWorkload($userIds);

        return $availableAssignments->map(function ($assignment) use ($workload, $phaseId, $applicationId) {
            $userId = $assignment->user_id;

            // Verificar si ya está asignado a esta fase
            $alreadyAssigned = false;
            if ($phaseId) {
                $alreadyAssigned = EvaluatorAssignment::where('user_id', $userId)
                    ->where('application_id', $applicationId)
                    ->where('phase_id', $phaseId)
                    ->exists();
            }

            return [
                'assignment_id' => $assignment->id,
                'user_id' => $userId,
                'name' => $assignment->user_name,
                'role' => $assignment->role_in_jury?->label(),
                'current_workload' => $workload[$userId] ?? 0,
                'already_assigned' => $alreadyAssigned,
            ];
        })->filter(function($evaluator) {
            return !$evaluator['already_assigned'];
        })->values();
    }

    /**
     * Asignación automática inteligente
     * Selecciona el mejor jurado disponible basado en carga de trabajo
     */
    public function autoAssign(string $applicationId, string $phaseId): EvaluatorAssignment
    {
        $application = \Modules\Application\Entities\Application::with('jobProfile')->findOrFail($applicationId);

        // Sugerir mejor jurado disponible
        $bestJuror = $this->juryAssignmentService->suggestBestJuror(
            $application->jobProfile->job_posting_id,
            $applicationId
        );

        if (!$bestJuror) {
            throw new \Exception('No hay jurados disponibles para asignar');
        }

        return $this->assignEvaluator([
            'user_id' => $bestJuror['user_id'],
            'application_id' => $applicationId,
            'phase_id' => $phaseId,
            'assignment_type' => 'AUTOMATIC',
        ]);
    }

    /**
     * Distribución automática de postulaciones entre jurados (Round-Robin con Fallback Inteligente)
     *
     * MEJORADO: Ahora maneja conflictos de interés intentando con otros jurados
     *
     * @param string $jobPostingId
     * @param string $phaseId
     * @param array $applicationIds Lista de IDs de postulaciones
     * @return array Resultado de asignaciones
     */
    public function distributeApplications(
        string $jobPostingId,
        string $phaseId,
        array $applicationIds
    ): array {
        // Obtener jurados activos
        $juryAssignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->get();

        if ($juryAssignments->isEmpty()) {
            throw new \Exception('No hay jurados asignados a la convocatoria');
        }

        $jurorCount = $juryAssignments->count();
        $assignments = [];
        $errors = [];
        $conflictErrors = [];
        $unassignableApplications = [];

        // Contador de asignaciones por jurado para mantener balance
        $jurorWorkload = array_fill_keys($juryAssignments->pluck('user_id')->toArray(), 0);

        // Distribuir equitativamente usando round-robin con fallback
        foreach ($applicationIds as $index => $applicationId) {
            $assigned = false;
            $attemptedJurors = [];

            // Intentar con el jurado que le toca por round-robin
            $startIndex = $index % $jurorCount;

            // Intentar con todos los jurados (empezando por el que le toca)
            for ($attempt = 0; $attempt < $jurorCount; $attempt++) {
                $jurorIndex = ($startIndex + $attempt) % $jurorCount;
                $juryAssignment = $juryAssignments[$jurorIndex];
                $userId = $juryAssignment->user_id;

                // Evitar reintentar con el mismo jurado
                if (in_array($userId, $attemptedJurors)) {
                    continue;
                }

                $attemptedJurors[] = $userId;

                try {
                    $assignment = $this->assignEvaluator([
                        'user_id' => $userId,
                        'application_id' => $applicationId,
                        'phase_id' => $phaseId,
                        'assignment_type' => 'AUTOMATIC',
                    ]);

                    $assignments[] = $assignment;
                    $jurorWorkload[$userId]++;
                    $assigned = true;

                    Log::info("Asignación exitosa", [
                        'applicationId' => $applicationId,
                        'userId' => $userId,
                        'attempt' => $attempt + 1,
                        'wasConflict' => $attempt > 0,
                    ]);

                    break; // Salir del loop de intentos
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();

                    // Distinguir entre conflictos de interés y otros errores
                    if (str_contains($errorMessage, 'conflicto') || str_contains($errorMessage, 'interés')) {
                        Log::info("Conflicto detectado, intentando con siguiente jurado", [
                            'applicationId' => $applicationId,
                            'userId' => $userId,
                            'attempt' => $attempt + 1,
                            'reason' => $errorMessage,
                        ]);

                        $conflictErrors[] = [
                            'application_id' => $applicationId,
                            'user_id' => $userId,
                            'error' => $errorMessage,
                        ];

                        // Si es el último intento, registrar como no asignable
                        if ($attempt === $jurorCount - 1) {
                            $unassignableApplications[] = [
                                'application_id' => $applicationId,
                                'reason' => 'Todos los jurados tienen conflictos de interés',
                                'attempted_jurors' => count($attemptedJurors),
                            ];

                            Log::warning("Postulación sin evaluador disponible", [
                                'applicationId' => $applicationId,
                                'attemptedJurors' => $attemptedJurors,
                                'reason' => 'Todos los jurados tienen conflictos de interés',
                            ]);
                        }

                        continue; // Intentar con siguiente jurado
                    } else {
                        // Error no relacionado con conflictos (ej: ya asignado)
                        Log::error("Error no relacionado con conflictos", [
                            'applicationId' => $applicationId,
                            'userId' => $userId,
                            'error' => $errorMessage,
                        ]);

                        $errors[] = [
                            'application_id' => $applicationId,
                            'user_id' => $userId,
                            'error' => $errorMessage,
                        ];

                        break; // No intentar con otros jurados para errores no conflicto
                    }
                }
            }
        }

        return [
            'success' => count($assignments),
            'errors' => count($errors),
            'conflicts' => count($conflictErrors),
            'unassignable' => count($unassignableApplications),
            'assignments' => $assignments,
            'error_details' => $errors,
            'conflict_details' => $conflictErrors,
            'unassignable_details' => $unassignableApplications,
            'juror_workload' => $jurorWorkload,
        ];
    }

    /**
     * Distribuir postulaciones automáticamente por convocatoria y fase
     * Obtiene todas las postulaciones de la convocatoria y las distribuye entre los jurados
     *
     * @param string $jobPostingId
     * @param string $phaseId
     * @param bool $onlyUnassigned Si true, solo asigna postulaciones sin asignación previa o evaluación
     * @return array
     */
    public function distributeByJobPosting(
        string $jobPostingId,
        string $phaseId,
        bool $onlyUnassigned = true
    ): array {
        // Obtener todos los job_profile_ids que pertenecen a esta convocatoria
        $jobProfileIds = \Modules\JobProfile\Entities\JobProfile::where('job_posting_id', $jobPostingId)
            ->pluck('id')
            ->toArray();

        if (empty($jobProfileIds)) {
            return [
                'success' => 0,
                'errors' => 0,
                'message' => 'No hay perfiles de puesto en esta convocatoria',
                'assignments' => [],
                'error_details' => [],
                'metrics' => [],
            ];
        }

        // Obtener todas las postulaciones de los perfiles de esta convocatoria
        $applicationsQuery = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE);

        // Si solo queremos las no asignadas, filtrar
        if ($onlyUnassigned) {
            // Excluir postulaciones con asignaciones activas
            $applicationsQuery->whereDoesntHave('evaluatorAssignments', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->active();
            });

            // MEJORADO: También excluir postulaciones con evaluaciones en progreso o completadas
            $applicationsQuery->whereDoesntHave('evaluations', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->whereIn('status', [
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::IN_PROGRESS->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::SUBMITTED->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::MODIFIED->value,
                      ]);
            });
        }

        $applications = $applicationsQuery->get();

        if ($applications->isEmpty()) {
            // Obtener métricas para entender por qué no hay postulaciones
            $metrics = $this->getDistributionMetrics($jobPostingId, $phaseId);

            return [
                'success' => 0,
                'errors' => 0,
                'message' => 'No hay postulaciones disponibles para asignar. Todas las postulaciones elegibles ya tienen asignación o evaluación.',
                'assignments' => [],
                'error_details' => [],
                'metrics' => $metrics,
            ];
        }

        // Obtener IDs de postulaciones
        $applicationIds = $applications->pluck('id')->toArray();

        // Obtener métricas antes de asignar
        $metricsBeforeAssignment = $this->getDistributionMetrics($jobPostingId, $phaseId);

        // Usar el método distributeApplications existente
        $result = $this->distributeApplications($jobPostingId, $phaseId, $applicationIds);

        $result['total_applications'] = count($applicationIds);
        $result['message'] = "Se asignaron {$result['success']} de {$result['total_applications']} postulaciones";
        $result['metrics'] = $metricsBeforeAssignment;

        return $result;
    }

    /**
     * Obtener métricas detalladas del estado de distribución
     *
     * @param string $jobPostingId
     * @param string $phaseId
     * @return array
     */
    public function getDistributionMetrics(string $jobPostingId, string $phaseId): array
    {
        // Obtener todos los job_profile_ids que pertenecen a esta convocatoria
        $jobProfileIds = \Modules\JobProfile\Entities\JobProfile::where('job_posting_id', $jobPostingId)
            ->pluck('id')
            ->toArray();

        if (empty($jobProfileIds)) {
            return [
                'total_eligible' => 0,
                'without_assignment' => 0,
                'with_assignment_no_evaluation' => 0,
                'with_evaluation_in_progress' => 0,
                'with_evaluation_completed' => 0,
                'available_to_assign' => 0,
            ];
        }

        // Total de postulaciones elegibles
        $totalEligible = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
            ->count();

        // Postulaciones sin asignación en esta fase
        $withoutAssignment = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
            ->whereDoesntHave('evaluatorAssignments', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)->active();
            })
            ->whereDoesntHave('evaluations', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId);
            })
            ->count();

        // Postulaciones con asignación pero sin evaluación creada
        $withAssignmentNoEvaluation = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
            ->whereHas('evaluatorAssignments', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)->active();
            })
            ->whereDoesntHave('evaluations', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId);
            })
            ->count();

        // Postulaciones con evaluación en progreso
        $withEvaluationInProgress = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
            ->whereHas('evaluations', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->whereIn('status', [
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::ASSIGNED->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::IN_PROGRESS->value,
                      ]);
            })
            ->count();

        // Postulaciones con evaluación completada
        $withEvaluationCompleted = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
            ->whereHas('evaluations', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->whereIn('status', [
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::SUBMITTED->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::MODIFIED->value,
                      ]);
            })
            ->count();

        // Disponibles para asignar (sin asignación activa ni evaluaciones en progreso/completadas)
        $availableToAssign = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE)
            ->whereDoesntHave('evaluatorAssignments', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)->active();
            })
            ->whereDoesntHave('evaluations', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->whereIn('status', [
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::IN_PROGRESS->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::SUBMITTED->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::MODIFIED->value,
                      ]);
            })
            ->count();

        return [
            'total_eligible' => $totalEligible,
            'without_assignment' => $withoutAssignment,
            'with_assignment_no_evaluation' => $withAssignmentNoEvaluation,
            'with_evaluation_in_progress' => $withEvaluationInProgress,
            'with_evaluation_completed' => $withEvaluationCompleted,
            'available_to_assign' => $availableToAssign,
        ];
    }

    /**
     * Reasignar evaluador
     */
    public function reassignEvaluator(
        string $assignmentId,
        int $newUserId,
        ?string $reason = null
    ): EvaluatorAssignment {
        $oldAssignment = EvaluatorAssignment::findOrFail($assignmentId);

        // Reasignar usando el método de la entidad
        $newAssignment = $oldAssignment->reassign($newUserId, auth()->id());

        // Registrar razón en metadata si se proporciona
        if ($reason) {
            $newAssignment->update([
                'metadata' => array_merge($newAssignment->metadata ?? [], [
                    'reassignment_reason' => $reason,
                    'previous_assignment_id' => $assignmentId,
                ])
            ]);
        }

        return $newAssignment->fresh();
    }

    /**
     * Remover asignación de evaluador
     */
    public function removeAssignment(string $assignmentId): bool
    {
        $assignment = EvaluatorAssignment::findOrFail($assignmentId);
        return $assignment->delete();
    }

    /**
     * Calcular carga de trabajo actual para usuarios
     *
     * @param array $userIds
     * @return array ['user_id' => count]
     */
    protected function calculateWorkload(array $userIds): array
    {
        $workload = EvaluatorAssignment::whereIn('user_id', $userIds)
            ->active()
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->pluck('count', 'user_id')
            ->toArray();

        // Rellenar con 0 los usuarios sin asignaciones
        foreach ($userIds as $userId) {
            if (!isset($workload[$userId])) {
                $workload[$userId] = 0;
            }
        }

        return $workload;
    }

    /**
     * Obtener estadísticas de asignación para una convocatoria
     */
    public function getAssignmentStatistics(string $jobPostingId, ?string $phaseId = null): array
    {
        $query = EvaluatorAssignment::where('job_posting_id', $jobPostingId);

        if ($phaseId) {
            $query->where('phase_id', $phaseId);
        }

        $assignments = $query->with('user')->get();

        $byUser = $assignments->groupBy('user_id');

        return [
            'total_assignments' => $assignments->count(),
            'total_evaluators' => $byUser->count(),
            'by_status' => [
                'pending' => $assignments->where('status', 'PENDING')->count(),
                'in_progress' => $assignments->where('status', 'IN_PROGRESS')->count(),
                'completed' => $assignments->where('status', 'COMPLETED')->count(),
            ],
            'workload_distribution' => $byUser->map(function ($userAssignments) {
                return [
                    'user_id' => $userAssignments->first()->user_id,
                    'name' => $userAssignments->first()->user->name ?? 'N/A',
                    'total' => $userAssignments->count(),
                    'pending' => $userAssignments->where('status', 'PENDING')->count(),
                    'completed' => $userAssignments->where('status', 'COMPLETED')->count(),
                ];
            })->values(),
        ];
    }

    /**
     * Distribución manual sin restricciones de conflictos
     *
     * ADVERTENCIA: Este método NO verifica conflictos de interés.
     * Usar solo cuando se necesite asignar manualmente sin restricciones.
     *
     * @param string $jobPostingId
     * @param string $phaseId
     * @param string $userId ID del jurado a asignar
     * @param bool $onlyUnassigned Si true, solo asigna postulaciones sin asignación previa
     * @return array
     */
    public function manualDistributeWithoutRestrictions(
        string $jobPostingId,
        string $phaseId,
        string $userId,
        bool $onlyUnassigned = true
    ): array {
        Log::info("Iniciando distribución manual sin restricciones", [
            'jobPostingId' => $jobPostingId,
            'phaseId' => $phaseId,
            'userId' => $userId,
            'onlyUnassigned' => $onlyUnassigned,
        ]);

        // Verificar que el usuario es un jurado asignado a la convocatoria
        $juryAssignment = JuryAssignment::where('user_id', $userId)
            ->where('job_posting_id', $jobPostingId)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$juryAssignment) {
            throw new \Exception('El usuario no está asignado como jurado en esta convocatoria');
        }

        if (!$juryAssignment->canEvaluate()) {
            throw new \Exception('El jurado no puede evaluar en este momento');
        }

        // Obtener todos los job_profile_ids que pertenecen a esta convocatoria
        $jobProfileIds = \Modules\JobProfile\Entities\JobProfile::where('job_posting_id', $jobPostingId)
            ->pluck('id')
            ->toArray();

        if (empty($jobProfileIds)) {
            return [
                'success' => 0,
                'errors' => 0,
                'message' => 'No hay perfiles de puesto en esta convocatoria',
                'assignments' => [],
                'error_details' => [],
            ];
        }

        // Obtener todas las postulaciones de los perfiles de esta convocatoria
        $applicationsQuery = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE);

        // Si solo queremos las no asignadas, filtrar
        if ($onlyUnassigned) {
            // Excluir postulaciones con asignaciones activas
            $applicationsQuery->whereDoesntHave('evaluatorAssignments', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->active();
            });

            // También excluir postulaciones con evaluaciones en progreso o completadas
            $applicationsQuery->whereDoesntHave('evaluations', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->whereIn('status', [
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::IN_PROGRESS->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::SUBMITTED->value,
                          \Modules\Evaluation\Enums\EvaluationStatusEnum::MODIFIED->value,
                      ]);
            });
        }

        $applications = $applicationsQuery->get();

        if ($applications->isEmpty()) {
            $metrics = $this->getDistributionMetrics($jobPostingId, $phaseId);

            return [
                'success' => 0,
                'errors' => 0,
                'message' => 'No hay postulaciones disponibles para asignar. Todas las postulaciones elegibles ya tienen asignación o evaluación.',
                'assignments' => [],
                'error_details' => [],
                'metrics' => $metrics,
            ];
        }

        $assignments = [];
        $errors = [];
        $skipped = [];

        // Asignar TODAS las postulaciones al mismo jurado SIN verificar conflictos
        foreach ($applications as $application) {
            try {
                // Verificar solo si ya existe asignación para esta combinación
                $existing = EvaluatorAssignment::where('user_id', $userId)
                    ->where('application_id', $application->id)
                    ->where('phase_id', $phaseId)
                    ->first();

                if ($existing) {
                    $skipped[] = [
                        'application_id' => $application->id,
                        'reason' => 'Ya existe asignación para este evaluador en esta fase',
                    ];
                    continue;
                }

                // Crear asignación SIN verificar conflictos
                $evaluatorAssignment = EvaluatorAssignment::create([
                    'user_id' => $userId,
                    'application_id' => $application->id,
                    'phase_id' => $phaseId,
                    'job_posting_id' => $jobPostingId,
                    'assignment_type' => 'MANUAL',
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'metadata' => [
                        'manual_distribution' => true,
                        'without_conflict_check' => true,
                        'assigned_at' => now()->toDateTimeString(),
                    ],
                ]);

                $assignments[] = $evaluatorAssignment;

                Log::info("Asignación manual creada sin verificar conflictos", [
                    'assignmentId' => $evaluatorAssignment->id,
                    'userId' => $userId,
                    'applicationId' => $application->id,
                ]);

            } catch (\Exception $e) {
                Log::error("Error en asignación manual", [
                    'applicationId' => $application->id,
                    'userId' => $userId,
                    'error' => $e->getMessage(),
                ]);

                $errors[] = [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $totalApplications = $applications->count();
        $successCount = count($assignments);
        $errorCount = count($errors);
        $skippedCount = count($skipped);

        Log::info("Distribución manual sin restricciones completada", [
            'total' => $totalApplications,
            'success' => $successCount,
            'errors' => $errorCount,
            'skipped' => $skippedCount,
        ]);

        return [
            'success' => $successCount,
            'errors' => $errorCount,
            'skipped' => $skippedCount,
            'total_applications' => $totalApplications,
            'message' => "Se asignaron {$successCount} de {$totalApplications} postulaciones al jurado seleccionado (sin verificar conflictos)",
            'assignments' => $assignments,
            'error_details' => $errors,
            'skipped_details' => $skipped,
        ];
    }
}
