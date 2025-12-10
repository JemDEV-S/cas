<?php

namespace Modules\Evaluation\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Modules\Evaluation\Entities\{EvaluatorAssignment, Evaluation};
use Modules\Evaluation\Enums\{AssignmentStatusEnum, EvaluationStatusEnum};
use Modules\Evaluation\Exceptions\EvaluationException;

class EvaluatorAssignmentService
{
    /**
     * Asignar evaluador manualmente a una postulación
     */
    public function assignEvaluator(array $data): EvaluatorAssignment
    {
        // Validar conflicto de interés
        if ($this->hasConflictOfInterest($data['evaluator_id'], $data['application_id'])) {
            throw new EvaluationException('El evaluador tiene un conflicto de interés con este postulante');
        }

        // Verificar que no exista asignación previa
        $existing = EvaluatorAssignment::where('evaluator_id', $data['evaluator_id'])
            ->where('application_id', $data['application_id'])
            ->where('phase_id', $data['phase_id'])
            ->first();

        if ($existing) {
            throw new EvaluationException('Ya existe una asignación para este evaluador en esta fase');
        }

        return DB::transaction(function () use ($data) {
            // Crear asignación
            $assignment = EvaluatorAssignment::create([
                'evaluator_id' => $data['evaluator_id'],
                'application_id' => $data['application_id'],
                'phase_id' => $data['phase_id'],
                'job_posting_id' => $data['job_posting_id'],
                'assignment_type' => 'MANUAL',
                'assigned_by' => auth()->id(),
                'deadline_at' => $data['deadline_at'] ?? now()->addDays(7),
                'workload_weight' => $data['workload_weight'] ?? 1,
            ]);

            // Crear evaluación asociada
            $evaluation = Evaluation::create([
                'application_id' => $data['application_id'],
                'evaluator_id' => $data['evaluator_id'],
                'phase_id' => $data['phase_id'],
                'job_posting_id' => $data['job_posting_id'],
                'status' => EvaluationStatusEnum::ASSIGNED,
                'deadline_at' => $assignment->deadline_at,
            ]);

            // Disparar evento
            event(new \Modules\Evaluation\Events\EvaluationAssigned($assignment));

            return $assignment->fresh('evaluator', 'application', 'phase');
        });
    }

    /**
     * Asignar evaluadores automáticamente (distribución equitativa)
     */
    public function autoAssignEvaluators(
        int $jobPostingId,
        int $phaseId,
        array $evaluatorIds,
        array $applicationIds
    ): Collection {
        return DB::transaction(function () use ($jobPostingId, $phaseId, $evaluatorIds, $applicationIds) {
            $assignments = collect();

            // Obtener carga actual de cada evaluador
            $evaluatorWorkload = $this->getEvaluatorWorkload($evaluatorIds, $phaseId);

            // Ordenar evaluadores por carga (menor a mayor)
            $sortedEvaluators = collect($evaluatorIds)->sortBy(function ($evaluatorId) use ($evaluatorWorkload) {
                return $evaluatorWorkload[$evaluatorId] ?? 0;
            });

            // Distribuir aplicaciones equitativamente
            foreach ($applicationIds as $index => $applicationId) {
                // Round-robin: asignar al evaluador con menos carga
                $evaluatorIndex = $index % $sortedEvaluators->count();
                $evaluatorId = $sortedEvaluators->values()[$evaluatorIndex];

                try {
                    $assignment = $this->assignEvaluator([
                        'evaluator_id' => $evaluatorId,
                        'application_id' => $applicationId,
                        'phase_id' => $phaseId,
                        'job_posting_id' => $jobPostingId,
                    ]);

                    $assignments->push($assignment);

                    // Actualizar carga del evaluador
                    $evaluatorWorkload[$evaluatorId] = ($evaluatorWorkload[$evaluatorId] ?? 0) + 1;

                    // Re-ordenar para mantener equidad
                    $sortedEvaluators = $sortedEvaluators->sortBy(function ($id) use ($evaluatorWorkload) {
                        return $evaluatorWorkload[$id] ?? 0;
                    });
                } catch (\Exception $e) {
                    // Log error y continuar con siguiente
                    \Log::warning("Error asignando evaluación: {$e->getMessage()}");
                }
            }

            return $assignments;
        });
    }

    /**
     * Reasignar evaluación a otro evaluador
     */
    public function reassignEvaluation(
        int $assignmentId,
        int $newEvaluatorId,
        string $reason
    ): EvaluatorAssignment {
        $assignment = EvaluatorAssignment::findOrFail($assignmentId);

        return DB::transaction(function () use ($assignment, $newEvaluatorId, $reason) {
            // Crear nueva asignación
            $newAssignment = $assignment->reassign($newEvaluatorId, auth()->id());

            // Cancelar evaluación anterior si existe
            $oldEvaluation = Evaluation::where('application_id', $assignment->application_id)
                ->where('phase_id', $assignment->phase_id)
                ->where('evaluator_id', $assignment->evaluator_id)
                ->first();

            if ($oldEvaluation && !$oldEvaluation->isCompleted()) {
                $oldEvaluation->update([
                    'status' => EvaluationStatusEnum::CANCELLED,
                    'metadata' => array_merge($oldEvaluation->metadata ?? [], [
                        'reassignment_reason' => $reason,
                        'reassigned_at' => now(),
                        'reassigned_by' => auth()->id(),
                    ]),
                ]);
            }

            return $newAssignment;
        });
    }

    /**
     * Obtener carga de trabajo de evaluadores
     */
    public function getEvaluatorWorkload(array $evaluatorIds, int $phaseId = null): array
    {
        $query = EvaluatorAssignment::whereIn('evaluator_id', $evaluatorIds)
            ->active();

        if ($phaseId) {
            $query->where('phase_id', $phaseId);
        }

        $assignments = $query->get();

        $workload = [];
        foreach ($evaluatorIds as $evaluatorId) {
            $workload[$evaluatorId] = $assignments
                ->where('evaluator_id', $evaluatorId)
                ->sum('workload_weight');
        }

        return $workload;
    }

    /**
     * Obtener asignaciones por evaluador
     */
    public function getEvaluatorAssignments(int $evaluatorId, array $filters = [])
    {
        $query = EvaluatorAssignment::with(['application', 'phase', 'jobPosting'])
            ->byEvaluator($evaluatorId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['phase_id'])) {
            $query->byPhase($filters['phase_id']);
        }

        if (isset($filters['pending_only']) && $filters['pending_only']) {
            $query->pending();
        }

        return $query->orderBy('deadline_at', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Verificar conflicto de interés
     */
    protected function hasConflictOfInterest(int $evaluatorId, int $applicationId): bool
    {
        // Aquí implementarías la lógica de detección de conflictos
        // Por ejemplo:
        // - Verificar si el evaluador es del mismo departamento
        // - Verificar si hay relación familiar
        // - Verificar registros de conflictos previos

        // Por ahora retornamos false (sin conflicto)
        return false;
    }

    /**
     * Notificar a evaluadores de nuevas asignaciones
     */
    public function notifyAssignments(Collection $assignments): void
    {
        foreach ($assignments as $assignment) {
            // Aquí integrarías con el sistema de notificaciones
            // Notification::send($assignment->evaluator, new EvaluationAssignedNotification($assignment));
            
            $assignment->markAsNotified();
        }
    }

    /**
     * Obtener estadísticas de asignaciones
     */
    public function getAssignmentStats(array $filters = []): array
    {
        $query = EvaluatorAssignment::query();

        if (isset($filters['job_posting_id'])) {
            $query->where('job_posting_id', $filters['job_posting_id']);
        }

        if (isset($filters['phase_id'])) {
            $query->where('phase_id', $filters['phase_id']);
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->pending()->count(),
            'in_progress' => (clone $query)->byStatus(AssignmentStatusEnum::IN_PROGRESS)->count(),
            'completed' => (clone $query)->completed()->count(),
            'overdue' => (clone $query)->overdue()->count(),
            'workload_by_evaluator' => $this->getWorkloadDistribution($query),
        ];
    }

    /**
     * Obtener distribución de carga por evaluador
     */
    protected function getWorkloadDistribution($query): array
    {
        return $query->get()
            ->groupBy('evaluator_id')
            ->map(function ($assignments) {
                return [
                    'evaluator' => $assignments->first()->evaluator->name ?? 'Unknown',
                    'total_assignments' => $assignments->count(),
                    'pending' => $assignments->where('status', AssignmentStatusEnum::PENDING)->count(),
                    'completed' => $assignments->where('status', AssignmentStatusEnum::COMPLETED)->count(),
                    'total_workload' => $assignments->sum('workload_weight'),
                ];
            })
            ->toArray();
    }
}