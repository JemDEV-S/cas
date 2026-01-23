<?php

namespace Modules\Jury\Services;

use Modules\Jury\Entities\{JuryAssignment, JuryConflict};
use Modules\Jury\Enums\{JuryRole, AssignmentStatus};
use Modules\User\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Servicio para gestionar asignaciones de jurados a convocatorias
 *
 * Según diseño optimizado:
 * - Usa user_id directamente (sin JuryMember)
 * - Sin gestión de workload (se calcula dinámicamente)
 * - Estado simple: ACTIVE/INACTIVE
 */
class JuryAssignmentService
{
    public function __construct(
        protected ConflictDetectionService $conflictService
    ) {}

    /**
     * Get all assignments with filters
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = JuryAssignment::with(['user', 'jobPosting']);

        if (!empty($filters['job_posting_id'])) {
            $query->byJobPosting($filters['job_posting_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (!empty($filters['role_in_jury'])) {
            $query->byRole($filters['role_in_jury']);
        }

        if (!empty($filters['dependency_scope_id'])) {
            $query->byDependency($filters['dependency_scope_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (!isset($filters['include_inactive'])) {
            $query->active();
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->ordered()->paginate($perPage);
    }

    /**
     * Get assignments for a job posting
     */
    public function getByJobPosting(string $jobPostingId): Collection
    {
        return JuryAssignment::with(['user'])
            ->byJobPosting($jobPostingId)
            ->ordered()
            ->get();
    }

    /**
     * Assign user (with JURADO role) to job posting
     *
     * @param array $data ['user_id', 'job_posting_id', 'role_in_jury', 'dependency_scope_id'?]
     */
    public function assign(array $data): JuryAssignment
    {
        // Validar que el usuario tiene rol JURADO
        $user = User::findOrFail($data['user_id']);

        if (!$user->hasRole('jury')) {
            throw new \Exception('El usuario debe tener el rol JURADO para ser asignado');
        }

        // Verificar duplicados
        $existing = JuryAssignment::byJobPosting($data['job_posting_id'])
            ->byUser($data['user_id'])
            ->active()
            ->first();

        if ($existing) {
            throw new \Exception('El usuario ya está asignado a esta convocatoria');
        }

        // Crear asignación
        $assignment = JuryAssignment::create(array_merge($data, [
            'assigned_by' => auth()->id(),
            'status' => AssignmentStatus::ACTIVE,
        ]));

        return $assignment->fresh(['user', 'jobPosting']);
    }

    /**
     * Auto-assign jury members to job posting based on workload balancing
     *
     * @param string $jobPostingId
     * @param int $totalJurors Número total de jurados a asignar
     * @param array|null $preferredRoles Roles específicos ['PRESIDENTE', 'VOCAL', ...]
     */
    public function autoAssign(
        string $jobPostingId,
        int $totalJurors = 3,
        ?array $preferredRoles = null
    ): array {
        // Obtener usuarios con rol JURADO disponibles
        $availableJurors = User::role('JURADO')
            ->whereNotIn('id', function($query) use ($jobPostingId) {
                $query->select('user_id')
                    ->from('jury_assignments')
                    ->where('job_posting_id', $jobPostingId)
                    ->where('status', AssignmentStatus::ACTIVE);
            })
            ->get();

        if ($availableJurors->count() < $totalJurors) {
            throw new \Exception('No hay suficientes jurados disponibles');
        }

        // Calcular carga actual de cada jurado
        $jurorWorkload = $this->calculateWorkloadForUsers($availableJurors->pluck('id')->toArray());

        // Ordenar por menor carga
        $sortedJurors = $availableJurors->sortBy(function($juror) use ($jurorWorkload) {
            return $jurorWorkload[$juror->id] ?? 0;
        });

        $assignments = [];
        $roles = $preferredRoles ?? [
            JuryRole::PRESIDENTE,
            JuryRole::VOCAL,
            JuryRole::VOCAL,
        ];

        // Asignar jurados
        foreach ($sortedJurors->take($totalJurors)->values() as $index => $juror) {
            $role = $roles[$index] ?? JuryRole::VOCAL;

            $assignments[] = $this->assign([
                'user_id' => $juror->id,
                'job_posting_id' => $jobPostingId,
                'role_in_jury' => $role,
            ]);
        }

        return $assignments;
    }

    /**
     * Deactivate assignment
     */
    public function deactivate(string $assignmentId): JuryAssignment
    {
        $assignment = JuryAssignment::findOrFail($assignmentId);
        $assignment->deactivate();

        return $assignment->fresh();
    }

    /**
     * Activate assignment
     */
    public function activate(string $assignmentId): JuryAssignment
    {
        $assignment = JuryAssignment::findOrFail($assignmentId);
        $assignment->activate();

        return $assignment->fresh();
    }

    /**
     * Get available evaluators for an application
     *
     * Retorna jurados que:
     * - Están asignados activamente a la convocatoria
     * - No tienen conflictos con la postulación
     * - Respetan el dependency_scope si está configurado
     */
    public function getAvailableEvaluators(
        string $jobPostingId,
        ?string $applicationId = null
    ): Collection {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->with('user')
            ->get();

        // Si hay application_id, excluir jurados con conflictos
        if ($applicationId) {
            $conflictedUserIds = JuryConflict::where('application_id', $applicationId)
                ->pluck('user_id')
                ->toArray();

            $assignments = $assignments->filter(function ($assignment) use ($conflictedUserIds) {
                return !in_array($assignment->user_id, $conflictedUserIds);
            });

            // Filtrar por dependency_scope si está configurado
            $application = \Modules\Application\Entities\Application::find($applicationId);
            if ($application) {
                $assignments = $assignments->filter(function ($assignment) use ($application) {
                    // Si el jurado tiene dependency_scope_id, verificar que coincida con la dependencia del perfil
                    if ($assignment->dependency_scope_id) {
                        return $assignment->dependency_scope_id == $application->profile->dependency_id;
                    }
                    return true; // Si no tiene scope, puede evaluar cualquiera
                });
            }
        }

        return $assignments;
    }

    /**
     * Get workload statistics for job posting
     * Calcula dinámicamente desde evaluator_assignments
     */
    public function getWorkloadStatistics(string $jobPostingId): array
    {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->with('user')
            ->get();

        $workload = $this->calculateWorkloadForUsers($assignments->pluck('user_id')->toArray());

        return [
            'total_assignments' => $assignments->count(),
            'members' => $assignments->map(function ($assignment) use ($workload) {
                $userId = $assignment->user_id;
                return [
                    'id' => $assignment->id,
                    'user_id' => $userId,
                    'name' => $assignment->user_name,
                    'role' => $assignment->role_in_jury?->label(),
                    'current_evaluations' => $workload[$userId] ?? 0,
                ];
            }),
        ];
    }

    /**
     * Calculate current workload for users
     *
     * @param array $userIds
     * @return array ['user_id' => count]
     */
    protected function calculateWorkloadForUsers(array $userIds): array
    {
        $workload = \Modules\Evaluation\Entities\EvaluatorAssignment::whereIn('user_id', $userIds)
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
     * Balance workload: suggest best juror for new assignment
     *
     * @return array|null ['user_id', 'name', 'current_workload']
     */
    public function suggestBestJuror(string $jobPostingId, ?string $applicationId = null): ?array
    {
        $available = $this->getAvailableEvaluators($jobPostingId, $applicationId);

        if ($available->isEmpty()) {
            return null;
        }

        $userIds = $available->pluck('user_id')->toArray();
        $workload = $this->calculateWorkloadForUsers($userIds);

        // Ordenar por menor carga
        $bestAssignment = $available->sortBy(function($assignment) use ($workload) {
            return $workload[$assignment->user_id] ?? 0;
        })->first();

        return [
            'assignment_id' => $bestAssignment->id,
            'user_id' => $bestAssignment->user_id,
            'name' => $bestAssignment->user_name,
            'role' => $bestAssignment->role_in_jury?->label(),
            'current_workload' => $workload[$bestAssignment->user_id] ?? 0,
        ];
    }
}
