<?php

namespace Modules\Jury\Services;

use Modules\Jury\Entities\{JuryMember, JuryAssignment};
use Illuminate\Support\Collection;

class WorkloadBalancerService
{
    /**
     * Balance workload for a job posting
     */
    public function balanceForJobPosting(string $jobPostingId): array
    {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->with('juryMember')
            ->get();

        if ($assignments->isEmpty()) {
            return ['message' => 'No hay asignaciones activas para balancear'];
        }

        $totalEvaluations = $assignments->sum('current_evaluations');
        $juryCount = $assignments->count();
        $targetPerJury = (int) ceil($totalEvaluations / $juryCount);

        $redistributions = [];

        foreach ($assignments as $assignment) {
            $current = $assignment->current_evaluations;
            $difference = $current - $targetPerJury;

            if (abs($difference) > 0) {
                $redistributions[] = [
                    'assignment_id' => $assignment->id,
                    'jury_member' => $assignment->jury_member_name,
                    'current' => $current,
                    'target' => $targetPerJury,
                    'change' => -$difference,
                ];
            }
        }

        return [
            'total_evaluations' => $totalEvaluations,
            'jury_count' => $juryCount,
            'target_per_jury' => $targetPerJury,
            'redistributions' => $redistributions,
            'balanced' => empty($redistributions),
        ];
    }

    /**
     * Suggest best jury member for new evaluation
     */
    public function suggestBestMember(string $jobPostingId): ?JuryAssignment
    {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->withWorkload()
            ->with('juryMember')
            ->get()
            ->filter(fn($a) => $a->hasCapacity());

        if ($assignments->isEmpty()) {
            return null;
        }

        // Return the one with lowest workload percentage
        return $assignments->sortBy('workload_percentage')->first();
    }

    /**
     * Get workload distribution
     */
    public function getDistribution(string $jobPostingId): array
    {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->with('juryMember.user')
            ->get();

        $stats = [
            'min_load' => $assignments->min('current_evaluations') ?? 0,
            'max_load' => $assignments->max('current_evaluations') ?? 0,
            'avg_load' => $assignments->avg('current_evaluations') ?? 0,
            'total_load' => $assignments->sum('current_evaluations'),
            'std_deviation' => $this->calculateStdDeviation(
                $assignments->pluck('current_evaluations')->toArray()
            ),
        ];

        $stats['is_balanced'] = $stats['std_deviation'] < 2; // Threshold

        return $stats;
    }

    /**
     * Distribute evaluations evenly
     */
    public function distributeEvaluations(
        string $jobPostingId,
        int $totalEvaluations
    ): array {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->get()
            ->filter(fn($a) => $a->hasCapacity());

        if ($assignments->isEmpty()) {
            throw new \Exception('No hay jurados disponibles con capacidad');
        }

        $distribution = [];
        $remaining = $totalEvaluations;
        $juryCount = $assignments->count();

        // Calculate base amount per jury
        $baseAmount = (int) floor($totalEvaluations / $juryCount);
        $extra = $totalEvaluations % $juryCount;

        // Sort by current workload (assign more to those with less)
        $sorted = $assignments->sortBy('current_evaluations');

        foreach ($sorted as $index => $assignment) {
            $amount = $baseAmount + ($index < $extra ? 1 : 0);
            
            // Check capacity
            $available = $assignment->getAvailableSlots();
            if ($available !== PHP_INT_MAX && $amount > $available) {
                $amount = $available;
            }

            $distribution[] = [
                'assignment_id' => $assignment->id,
                'jury_member' => $assignment->jury_member_name,
                'current_load' => $assignment->current_evaluations,
                'to_assign' => $amount,
                'new_total' => $assignment->current_evaluations + $amount,
            ];

            $remaining -= $amount;
        }

        return [
            'total_to_distribute' => $totalEvaluations,
            'distributed' => $totalEvaluations - $remaining,
            'remaining' => $remaining,
            'distribution' => $distribution,
        ];
    }

    /**
     * Calculate standard deviation
     */
    protected function calculateStdDeviation(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        $count = count($values);
        $mean = array_sum($values) / $count;
        
        $variance = array_reduce($values, function ($carry, $value) use ($mean) {
            return $carry + pow($value - $mean, 2);
        }, 0) / $count;

        return sqrt($variance);
    }

    /**
     * Get overloaded members
     */
    public function getOverloadedMembers(?string $jobPostingId = null): Collection
    {
        $query = JuryAssignment::active()->with('juryMember.user');

        if ($jobPostingId) {
            $query->byJobPosting($jobPostingId);
        }

        return $query->get()->filter(fn($a) => $a->isOverloaded());
    }

    /**
     * Get available capacity
     */
    public function getAvailableCapacity(string $jobPostingId): int
    {
        $assignments = JuryAssignment::byJobPosting($jobPostingId)
            ->active()
            ->get();

        return $assignments->sum(fn($a) => $a->getAvailableSlots());
    }
}