<?php

namespace Modules\Jury\Services;

use Modules\Jury\Entities\{JuryMember, JuryAssignment, JuryHistory};
use Modules\Jury\Enums\{MemberType, JuryRole, AssignmentStatus};
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JuryAssignmentService
{
    public function __construct(
        protected ConflictDetectionService $conflictService,
        protected WorkloadBalancerService $workloadService
    ) {}

    /**
     * Get all assignments with filters
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = JuryAssignment::with(['juryMember.user', 'jobPosting']);

        if (!empty($filters['job_posting_id'])) {
            $query->byJobPosting($filters['job_posting_id']);
        }

        if (!empty($filters['jury_member_id'])) {
            $query->byJuryMember($filters['jury_member_id']);
        }

        if (!empty($filters['member_type'])) {
            $query->where('member_type', $filters['member_type']);
        }

        if (!empty($filters['role_in_jury'])) {
            $query->where('role_in_jury', $filters['role_in_jury']);
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
        return JuryAssignment::with(['juryMember.user'])
            ->byJobPosting($jobPostingId)
            ->ordered()
            ->get();
    }

    /**
     * Assign jury member to job posting
     */
    public function assign(array $data): JuryAssignment
    {
        // Validar que el jurado puede ser asignado
        $juryMember = JuryMember::findOrFail($data['jury_member_id']);
        
        if (!$juryMember->canBeAssigned()) {
            throw new \Exception('El jurado no está disponible para asignación');
        }

        // Verificar duplicados
        $existing = JuryAssignment::byJobPosting($data['job_posting_id'])
            ->byJuryMember($data['jury_member_id'])
            ->where('member_type', $data['member_type'])
            ->active()
            ->first();

        if ($existing) {
            throw new \Exception('El jurado ya está asignado a esta convocatoria con el mismo tipo');
        }

        // Crear asignación
        $assignment = JuryAssignment::create(array_merge($data, [
            'assigned_by' => auth()->id(),
            'status' => AssignmentStatus::ACTIVE,
            'is_active' => true,
        ]));

        // Registrar en historial
        JuryHistory::logAssignment(
            $assignment->id,
            $assignment->jury_member_id,
            $assignment->job_posting_id
        );

        // Actualizar contador de jurado
        $juryMember->increment('total_assignments');

        return $assignment->fresh(['juryMember.user', 'jobPosting']);
    }

    /**
     * Auto-assign jury members to job posting
     */
    public function autoAssign(
        string $jobPostingId,
        int $totalTitulares = 3,
        int $totalSuplentes = 2,
        ?array $preferredSpecialties = null
    ): array {
        $availableMembers = JuryMember::active()
            ->available()
            ->trained()
            ->withWorkload()
            ->get()
            ->filter(fn($m) => !$m->isOverloaded());

        if ($preferredSpecialties) {
            $availableMembers = $availableMembers->filter(function ($member) use ($preferredSpecialties) {
                return in_array($member->specialty, $preferredSpecialties);
            });
        }

        if ($availableMembers->count() < ($totalTitulares + $totalSuplentes)) {
            throw new \Exception('No hay suficientes jurados disponibles');
        }

        // Ordenar por carga de trabajo (menor carga primero)
        $sorted = $availableMembers->sortBy(fn($m) => $m->workload_percentage);

        $assignments = [];

        // Asignar titulares
        $titulares = $sorted->take($totalTitulares);
        foreach ($titulares->values() as $index => $member) {
            $role = match($index) {
                0 => JuryRole::PRESIDENTE,
                1 => JuryRole::SECRETARIO,
                default => JuryRole::VOCAL,
            };

            $assignments[] = $this->assign([
                'jury_member_id' => $member->id,
                'job_posting_id' => $jobPostingId,
                'member_type' => MemberType::TITULAR,
                'role_in_jury' => $role,
                'order' => $index + 1,
            ]);
        }

        // Asignar suplentes
        $suplentes = $sorted->skip($totalTitulares)->take($totalSuplentes);
        foreach ($suplentes->values() as $index => $member) {
            $assignments[] = $this->assign([
                'jury_member_id' => $member->id,
                'job_posting_id' => $jobPostingId,
                'member_type' => MemberType::SUPLENTE,
                'role_in_jury' => JuryRole::MIEMBRO,
                'order' => $totalTitulares + $index + 1,
            ]);
        }

        return $assignments;
    }

    /**
     * Replace jury member
     */
    public function replace(
        string $assignmentId,
        string $newJuryMemberId,
        string $reason
    ): JuryAssignment {
        $oldAssignment = JuryAssignment::findOrFail($assignmentId);
        $newMember = JuryMember::findOrFail($newJuryMemberId);

        if (!$newMember->canBeAssigned()) {
            throw new \Exception('El nuevo jurado no está disponible');
        }

        // Marcar la asignación anterior como reemplazada
        $oldAssignment->replace(
            $newJuryMemberId,
            $reason,
            auth()->id()
        );

        // Crear nueva asignación con los mismos datos
        $newAssignment = $this->assign([
            'jury_member_id' => $newJuryMemberId,
            'job_posting_id' => $oldAssignment->job_posting_id,
            'member_type' => $oldAssignment->member_type,
            'role_in_jury' => $oldAssignment->role_in_jury,
            'order' => $oldAssignment->order,
            'max_evaluations' => $oldAssignment->max_evaluations,
        ]);

        // Registrar en historial
        JuryHistory::logReplacement(
            $oldAssignment->id,
            $oldAssignment->jury_member_id,
            $newJuryMemberId,
            $reason
        );

        return $newAssignment;
    }

    /**
     * Excuse jury member from assignment
     */
    public function excuse(string $assignmentId, string $reason): JuryAssignment
    {
        $assignment = JuryAssignment::findOrFail($assignmentId);
        $assignment->excuse($reason, auth()->id());

        JuryHistory::log([
            'jury_assignment_id' => $assignment->id,
            'jury_member_id' => $assignment->jury_member_id,
            'job_posting_id' => $assignment->job_posting_id,
            'event_type' => 'EXCUSED',
            'description' => 'Jurado excusado de la asignación',
            'reason' => $reason,
            'performed_by' => auth()->id(),
        ]);

        return $assignment->fresh();
    }

    /**
     * Remove assignment
     */
    public function remove(string $assignmentId): JuryAssignment
    {
        $assignment = JuryAssignment::findOrFail($assignmentId);
        $assignment->remove();

        JuryHistory::log([
            'jury_assignment_id' => $assignment->id,
            'jury_member_id' => $assignment->jury_member_id,
            'job_posting_id' => $assignment->job_posting_id,
            'event_type' => 'REMOVED',
            'description' => 'Asignación removida',
            'performed_by' => auth()->id(),
        ]);

        return $assignment->fresh();
    }

    /**
     * Update workload for assignment
     */
    public function updateWorkload(string $assignmentId, int $evaluationsAdded = 0): JuryAssignment
    {
        $assignment = JuryAssignment::findOrFail($assignmentId);

        if ($evaluationsAdded > 0) {
            $assignment->incrementWorkload($evaluationsAdded);
        } elseif ($evaluationsAdded < 0) {
            $assignment->decrementWorkload(abs($evaluationsAdded));
        }

        JuryHistory::log([
            'jury_assignment_id' => $assignment->id,
            'jury_member_id' => $assignment->jury_member_id,
            'event_type' => 'WORKLOAD_UPDATED',
            'description' => 'Carga de trabajo actualizada',
            'metadata' => [
                'change' => $evaluationsAdded,
                'current_evaluations' => $assignment->current_evaluations,
            ],
            'performed_by' => auth()->id(),
        ]);

        return $assignment->fresh();
    }

    /**
     * Get workload statistics for job posting
     */
    public function getWorkloadStatistics(string $jobPostingId): array
    {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->with('juryMember.user')
            ->get();

        return [
            'total_assignments' => $assignments->count(),
            'total_evaluations' => $assignments->sum('current_evaluations'),
            'completed_evaluations' => $assignments->sum('completed_evaluations'),
            'members' => $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'jury_member_name' => $assignment->jury_member_name,
                    'member_type' => $assignment->member_type->label(),
                    'role' => $assignment->role_in_jury?->label(),
                    'current_evaluations' => $assignment->current_evaluations,
                    'max_evaluations' => $assignment->max_evaluations,
                    'completed_evaluations' => $assignment->completed_evaluations,
                    'workload_percentage' => $assignment->workload_percentage,
                    'has_capacity' => $assignment->hasCapacity(),
                ];
            }),
        ];
    }

    /**
     * Balance workload across jury members
     */
    public function balanceWorkload(string $jobPostingId): array
    {
        return $this->workloadService->balanceForJobPosting($jobPostingId);
    }

    /**
     * Get available evaluators for application
     */
    public function getAvailableEvaluators(
        string $jobPostingId,
        ?string $phaseId = null,
        ?string $applicationId = null
    ): Collection {
        $query = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->with('juryMember.user');

        $assignments = $query->get()->filter(function ($assignment) {
            return $assignment->hasCapacity();
        });

        // Si hay application_id, excluir jurados con conflictos
        if ($applicationId) {
            $conflictedIds = $this->conflictService
                ->getConflictedJuryMembers($applicationId)
                ->pluck('id')
                ->toArray();

            $assignments = $assignments->filter(function ($assignment) use ($conflictedIds) {
                return !in_array($assignment->jury_member_id, $conflictedIds);
            });
        }

        return $assignments;
    }
}