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

        // 2. Verificar conflictos de interés
        $hasConflict = JuryConflict::hasConflict($data['user_id'], $data['application_id']);
        if ($hasConflict) {
            Log::warning("Conflicto de interés detectado", [
                'userId' => $data['user_id'],
                'applicationId' => $data['application_id'],
            ]);
            throw new \Exception('El jurado tiene un conflicto de interés declarado con esta postulación');
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
     * Distribución automática de postulaciones entre jurados (Round-Robin)
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

        // Distribuir equitativamente usando round-robin
        foreach ($applicationIds as $index => $applicationId) {
            $jurorIndex = $index % $jurorCount;
            $juryAssignment = $juryAssignments[$jurorIndex];

            try {
                $assignments[] = $this->assignEvaluator([
                    'user_id' => $juryAssignment->user_id,
                    'application_id' => $applicationId,
                    'phase_id' => $phaseId,
                    'assignment_type' => 'AUTOMATIC',
                ]);
            } catch (\Exception $e) {
                $errors[] = [
                    'application_id' => $applicationId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => count($assignments),
            'errors' => count($errors),
            'assignments' => $assignments,
            'error_details' => $errors,
        ];
    }

    /**
     * Distribuir postulaciones automáticamente por convocatoria y fase
     * Obtiene todas las postulaciones de la convocatoria y las distribuye entre los jurados
     *
     * @param string $jobPostingId
     * @param string $phaseId
     * @param bool $onlyUnassigned Si true, solo asigna postulaciones sin asignación previa
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
            ];
        }

        // Obtener todas las postulaciones de los perfiles de esta convocatoria
        $applicationsQuery = \Modules\Application\Entities\Application::whereIn('job_profile_id', $jobProfileIds)
            ->where('status', \Modules\Application\Enums\ApplicationStatus::ELIGIBLE);

        // Si solo queremos las no asignadas, filtrar
        if ($onlyUnassigned) {
            $applicationsQuery->whereDoesntHave('evaluatorAssignments', function($query) use ($phaseId) {
                $query->where('phase_id', $phaseId)
                      ->active();
            });
        }

        $applications = $applicationsQuery->get();

        if ($applications->isEmpty()) {
            return [
                'success' => 0,
                'errors' => 0,
                'message' => 'No hay postulaciones disponibles para asignar',
                'assignments' => [],
                'error_details' => [],
            ];
        }

        // Obtener IDs de postulaciones
        $applicationIds = $applications->pluck('id')->toArray();

        // Usar el método distributeApplications existente
        $result = $this->distributeApplications($jobPostingId, $phaseId, $applicationIds);

        $result['total_applications'] = count($applicationIds);
        $result['message'] = "Se asignaron {$result['success']} de {$result['total_applications']} postulaciones";

        return $result;
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
}
