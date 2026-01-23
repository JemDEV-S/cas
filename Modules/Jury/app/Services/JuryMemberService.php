<?php

namespace Modules\Jury\Services;

use Modules\Jury\Entities\{JuryMember, JuryHistory};
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\User\Services\UserService;

class JuryMemberService
{
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get all jury members with filters
     */
    public function getAll(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 10;

        $query = $this->userService
            ->juryUsersQuery(); // scope

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
