<?php

namespace Modules\Jury\Services;

use Modules\Jury\Entities\{JuryMember, JuryHistory};
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JuryMemberService
{
    /**
     * Get all jury members with filters
     */
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = JuryMember::with('user')->withWorkload();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (isset($filters['is_available'])) {
            if ($filters['is_available']) {
                $query->available();
            } else {
                $query->where('is_available', false);
            }
        }

        if (isset($filters['training_completed'])) {
            $query->where('training_completed', (bool) $filters['training_completed']);
        }

        if (!empty($filters['specialty'])) {
            $query->bySpecialty($filters['specialty']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get available jury members for assignment
     */
    public function getAvailableForAssignment(array $filters = []): Collection
    {
        $query = JuryMember::active()
            ->available()
            ->trained()
            ->withWorkload();

        if (!empty($filters['specialty'])) {
            $query->bySpecialty($filters['specialty']);
        }

        if (!empty($filters['exclude_ids'])) {
            $query->whereNotIn('id', $filters['exclude_ids']);
        }

        // Filtrar solo los que no están sobrecargados
        return $query->get()->filter(function ($member) {
            return !$member->isOverloaded();
        });
    }

    /**
     * Create jury member
     */
    public function create(array $data): JuryMember
    {
        $member = JuryMember::create($data);

        JuryHistory::log([
            'jury_member_id' => $member->id,
            'event_type' => 'CREATED',
            'description' => 'Jurado registrado en el sistema',
            'performed_by' => auth()->id(),
        ]);

        return $member->fresh('user');
    }

    /**
     * Update jury member
     */
    public function update(string $id, array $data): JuryMember
    {
        $member = JuryMember::findOrFail($id);
        $oldValues = $member->only(array_keys($data));

        $member->update($data);

        JuryHistory::log([
            'jury_member_id' => $member->id,
            'event_type' => 'UPDATED',
            'description' => 'Información del jurado actualizada',
            'old_values' => $oldValues,
            'new_values' => $data,
            'performed_by' => auth()->id(),
        ]);

        return $member->fresh('user');
    }

    /**
     * Delete jury member
     */
    public function delete(string $id): bool
    {
        $member = JuryMember::findOrFail($id);

        // Verificar que no tenga asignaciones activas
        $activeAssignments = $member->assignments()->active()->count();
        if ($activeAssignments > 0) {
            throw new \Exception("No se puede eliminar. El jurado tiene {$activeAssignments} asignaciones activas.");
        }

        JuryHistory::log([
            'jury_member_id' => $member->id,
            'event_type' => 'DELETED',
            'description' => 'Jurado eliminado del sistema',
            'performed_by' => auth()->id(),
        ]);

        return $member->delete();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(string $id): JuryMember
    {
        $member = JuryMember::findOrFail($id);
        $newStatus = !$member->is_active;

        $member->update(['is_active' => $newStatus]);

        JuryHistory::logStatusChange(
            assignmentId: null,
            juryMemberId: $member->id,
            oldStatus: $member->is_active ? 'ACTIVE' : 'INACTIVE',
            newStatus: $newStatus ? 'ACTIVE' : 'INACTIVE',
            reason: $newStatus ? 'Jurado activado' : 'Jurado desactivado'
        );

        return $member->fresh();
    }

    /**
     * Mark as unavailable
     */
    public function markAsUnavailable(
        string $id,
        string $reason,
        ?\DateTime $from = null,
        ?\DateTime $until = null
    ): JuryMember {
        $member = JuryMember::findOrFail($id);
        $member->markAsUnavailable($reason, $from, $until);

        JuryHistory::log([
            'jury_member_id' => $member->id,
            'event_type' => 'UNAVAILABLE',
            'description' => 'Jurado marcado como no disponible',
            'reason' => $reason,
            'metadata' => [
                'from' => $from?->format('Y-m-d'),
                'until' => $until?->format('Y-m-d'),
            ],
            'performed_by' => auth()->id(),
        ]);

        return $member->fresh();
    }

    /**
     * Mark as available
     */
    public function markAsAvailable(string $id): JuryMember
    {
        $member = JuryMember::findOrFail($id);
        $member->markAsAvailable();

        JuryHistory::log([
            'jury_member_id' => $member->id,
            'event_type' => 'AVAILABLE',
            'description' => 'Jurado marcado como disponible',
            'performed_by' => auth()->id(),
        ]);

        return $member->fresh();
    }

    /**
     * Complete training
     */
    public function completeTraining(string $id, ?string $certificatePath = null): JuryMember
    {
        $member = JuryMember::findOrFail($id);
        $member->completeTraining($certificatePath);

        JuryHistory::logTrainingCompleted($member->id);

        return $member->fresh();
    }

    /**
     * Get statistics for a jury member
     */
    public function getStatistics(string $id): array
    {
        $member = JuryMember::withWorkload()->findOrFail($id);

        return [
            'total_assignments' => $member->total_assignments,
            'total_evaluations' => $member->total_evaluations,
            'active_assignments' => $member->active_assignments_count ?? 0,
            'current_workload' => $member->getCurrentWorkload(),
            'max_capacity' => $member->max_concurrent_assignments,
            'available_capacity' => $member->getAvailableCapacity(),
            'workload_percentage' => $member->workload_percentage,
            'average_evaluation_time' => $member->average_evaluation_time,
            'consistency_score' => $member->consistency_score,
            'average_rating' => $member->average_rating,
            'is_overloaded' => $member->isOverloaded(),
            'can_be_assigned' => $member->canBeAssigned(),
        ];
    }

    /**
     * Update statistics
     */
    public function updateStatistics(string $id, array $stats): JuryMember
    {
        $member = JuryMember::findOrFail($id);
        $member->updateStatistics($stats);

        return $member->fresh();
    }

    /**
     * Get workload summary for all members
     */
    public function getWorkloadSummary(): array
    {
        $members = JuryMember::active()
            ->withWorkload()
            ->get();

        return [
            'total_members' => $members->count(),
            'available_members' => $members->filter(fn($m) => $m->canBeAssigned())->count(),
            'overloaded_members' => $members->filter(fn($m) => $m->isOverloaded())->count(),
            'total_capacity' => $members->sum('max_concurrent_assignments'),
            'used_capacity' => $members->sum(fn($m) => $m->getCurrentWorkload()),
            'available_capacity' => $members->sum(fn($m) => $m->getAvailableCapacity()),
            'members' => $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->full_name,
                    'current_workload' => $member->getCurrentWorkload(),
                    'max_capacity' => $member->max_concurrent_assignments,
                    'workload_percentage' => $member->workload_percentage,
                    'is_overloaded' => $member->isOverloaded(),
                ];
            }),
        ];
    }
}