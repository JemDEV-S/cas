<?php

namespace Modules\Jury\Services;

use Modules\Jury\Entities\{JuryMember, JuryConflict, JuryHistory};
use Modules\Jury\Enums\{ConflictType, ConflictSeverity, ConflictStatus};
use Illuminate\Support\Collection;

class ConflictDetectionService
{
    /**
     * Report a conflict
     */
    public function report(array $data): JuryConflict
    {
        // Auto-detect recommended severity
        if (empty($data['severity']) && !empty($data['conflict_type'])) {
            $type = ConflictType::from($data['conflict_type']);
            $data['severity'] = $type->recommendedSeverity();
        }

        $conflict = JuryConflict::create(array_merge($data, [
            'reported_by' => $data['reported_by'] ?? auth()->id(),
            'status' => ConflictStatus::REPORTED,
        ]));

        JuryHistory::logConflictReported(
            $conflict->jury_member_id,
            $conflict->conflict_type->value,
            $conflict->application_id,
            $conflict->job_posting_id
        );

        return $conflict->fresh();
    }

    /**
     * Auto-detect potential conflicts
     */
    public function autoDetect(string $juryMemberId, string $applicationId): array
    {
        $detectedConflicts = [];
        
        $juryMember = JuryMember::with('user')->findOrFail($juryMemberId);
        $application = \Modules\Application\Entities\Application::with('applicant')->findOrFail($applicationId);

        // 1. Check if they share email domain (possible organization relationship)
        if ($this->shareEmailDomain($juryMember->email, $application->email)) {
            $detectedConflicts[] = [
                'type' => ConflictType::PROFESSIONAL,
                'severity' => ConflictSeverity::MEDIUM,
                'description' => 'Comparten dominio de correo electrónico',
            ];
        }

        // 2. Check if jury member previously evaluated this applicant
        $previousEvaluations = $this->getPreviousEvaluations($juryMemberId, $application->applicant_id);
        if ($previousEvaluations > 0) {
            $detectedConflicts[] = [
                'type' => ConflictType::PRIOR_EVALUATION,
                'severity' => ConflictSeverity::MEDIUM,
                'description' => "Ha evaluado a este postulante {$previousEvaluations} vez(ces) anteriormente",
            ];
        }

        // 3. Check existing conflicts for this applicant
        $existingConflicts = JuryConflict::byJuryMember($juryMemberId)
            ->where('applicant_id', $application->applicant_id)
            ->where('status', '!=', ConflictStatus::DISMISSED)
            ->exists();

        if ($existingConflicts) {
            $detectedConflicts[] = [
                'type' => ConflictType::OTHER,
                'severity' => ConflictSeverity::HIGH,
                'description' => 'Existe un conflicto previo reportado con este postulante',
            ];
        }

        return $detectedConflicts;
    }

    /**
     * Get conflicted jury members for an application
     */
    public function getConflictedJuryMembers(string $applicationId): Collection
    {
        return JuryMember::whereHas('conflicts', function ($query) use ($applicationId) {
            $query->where('application_id', $applicationId)
                ->whereIn('status', [
                    ConflictStatus::REPORTED,
                    ConflictStatus::UNDER_REVIEW,
                    ConflictStatus::CONFIRMED,
                ]);
        })->get();
    }

    /**
     * Move conflict to review
     */
    public function moveToReview(string $conflictId, ?string $notes = null): JuryConflict
    {
        $conflict = JuryConflict::findOrFail($conflictId);
        $conflict->moveToReview(auth()->id(), $notes);

        return $conflict->fresh();
    }

    /**
     * Confirm conflict
     */
    public function confirm(string $conflictId, ?string $notes = null): JuryConflict
    {
        $conflict = JuryConflict::findOrFail($conflictId);
        $conflict->confirm(auth()->id(), $notes);

        return $conflict->fresh();
    }

    /**
     * Dismiss conflict
     */
    public function dismiss(string $conflictId, string $resolution): JuryConflict
    {
        $conflict = JuryConflict::findOrFail($conflictId);
        $conflict->dismiss($resolution, auth()->id());

        JuryHistory::log([
            'jury_member_id' => $conflict->jury_member_id,
            'event_type' => 'CONFLICT_DISMISSED',
            'description' => 'Conflicto desestimado',
            'reason' => $resolution,
            'metadata' => ['conflict_id' => $conflict->id],
            'performed_by' => auth()->id(),
        ]);

        return $conflict->fresh();
    }

    /**
     * Resolve conflict
     */
    public function resolve(
        string $conflictId,
        string $resolution,
        string $actionTaken,
        ?string $actionNotes = null
    ): JuryConflict {
        $conflict = JuryConflict::findOrFail($conflictId);
        $conflict->resolve($resolution, $actionTaken, $actionNotes, auth()->id());

        JuryHistory::log([
            'jury_member_id' => $conflict->jury_member_id,
            'event_type' => 'CONFLICT_RESOLVED',
            'description' => 'Conflicto resuelto',
            'reason' => $resolution,
            'metadata' => [
                'conflict_id' => $conflict->id,
                'action_taken' => $actionTaken,
            ],
            'performed_by' => auth()->id(),
        ]);

        return $conflict->fresh();
    }

    /**
     * Excuse jury member due to conflict
     */
    public function excuseJuryMember(string $conflictId, ?string $notes = null): JuryConflict
    {
        $conflict = JuryConflict::findOrFail($conflictId);
        $conflict->excuseJuryMember($notes);

        // También excusar de la asignación si existe
        if ($conflict->application_id) {
            $application = \Modules\Application\Entities\Application::find($conflict->application_id);
            if ($application) {
                $assignment = \Modules\Jury\Entities\JuryAssignment::byJobPosting($application->job_profile_vacancy_id)
                    ->byJuryMember($conflict->jury_member_id)
                    ->active()
                    ->first();

                if ($assignment) {
                    $assignment->excuse("Conflicto de interés: {$conflict->conflict_type->label()}", auth()->id());
                }
            }
        }

        return $conflict->fresh();
    }

    /**
     * Get conflict statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = JuryConflict::query();

        if (!empty($filters['job_posting_id'])) {
            $query->byJobPosting($filters['job_posting_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->where('reported_at', '>=', $filters['from_date']);
        }

        $all = $query->get();

        return [
            'total' => $all->count(),
            'by_status' => [
                'pending' => $all->filter(fn($c) => $c->isPending())->count(),
                'closed' => $all->filter(fn($c) => $c->isClosed())->count(),
            ],
            'by_severity' => [
                'low' => $all->where('severity', ConflictSeverity::LOW)->count(),
                'medium' => $all->where('severity', ConflictSeverity::MEDIUM)->count(),
                'high' => $all->where('severity', ConflictSeverity::HIGH)->count(),
                'critical' => $all->where('severity', ConflictSeverity::CRITICAL)->count(),
            ],
            'by_type' => $all->groupBy('conflict_type')->map->count(),
            'high_priority' => $all->filter(fn($c) => $c->requiresImmediateAction())->count(),
            'self_reported' => $all->where('is_self_reported', true)->count(),
            'average_resolution_time' => $all->filter(fn($c) => $c->isClosed())->avg('days_open'),
        ];
    }

    /**
     * Helper: Check if emails share domain
     */
    protected function shareEmailDomain(string $email1, string $email2): bool
    {
        $domain1 = explode('@', $email1)[1] ?? '';
        $domain2 = explode('@', $email2)[1] ?? '';

        return !empty($domain1) && $domain1 === $domain2;
    }

    /**
     * Helper: Get previous evaluations count
     */
    protected function getPreviousEvaluations(string $juryMemberId, string $applicantId): int
    {
        // This would query Evaluation module
        // For now return 0
        return 0;
    }
}