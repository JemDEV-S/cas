<?php

namespace Modules\Evaluation\Services;

use Modules\Evaluation\Entities\EvaluatorAssignment;
use Modules\Jury\Services\{JuryAssignmentService, ConflictDetectionService};
use Illuminate\Support\Collection;

class EvaluatorAssignmentService
{
    public function __construct(
        protected JuryAssignmentService $juryAssignmentService,
        protected ConflictDetectionService $conflictService
    ) {}

    /**
     * Asignar evaluador a postulación
     * ACTUALIZADO: Ahora usa jurados de JuryAssignment
     */
    public function assignEvaluator(array $data): EvaluatorAssignment
    {
        // 1. Verificar que el evaluador es un jurado asignado a la convocatoria
        $application = \Modules\Application\Entities\Application::findOrFail($data['application_id']);

        $juryAssignment = \Modules\Jury\Entities\JuryAssignment::where('jury_member_id', $data['evaluator_id'])
            ->where('job_posting_id', $application->job_profile_vacancy_id)
            ->where('status', 'ACTIVE')
            ->where('is_active', true)
            ->firstOrFail();

        if (!$juryAssignment->canEvaluate()) {
            throw new \Exception('El jurado no puede evaluar (está sobrecargado o inactivo)');
        }

        // 2. Verificar conflictos de interés
        $conflicts = $this->conflictService->autoDetect($data['evaluator_id'], $data['application_id']);

        if (!empty($conflicts)) {
            $highPriority = collect($conflicts)->filter(fn($c) =>
                in_array($c['severity']->value ?? $c['severity'], ['HIGH', 'CRITICAL'])
            );

            if ($highPriority->count() > 0) {
                throw new \Exception('El jurado tiene conflictos de interés que impiden la evaluación');
            }
        }

        // 3. Crear asignación de evaluador
        $evaluatorAssignment = EvaluatorAssignment::create([
            'application_id' => $data['application_id'],
            'evaluator_id' => $data['evaluator_id'], // Este es el jury_member_id
            'phase_id' => $data['phase_id'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        // 4. Incrementar carga del jurado
        $juryAssignment->incrementWorkload(1);

        return $evaluatorAssignment;
    }

    /**
     * Obtener evaluadores disponibles para una postulación
     * NUEVO: Filtra solo jurados asignados a la convocatoria
     */
    public function getAvailableEvaluators(
        string $applicationId,
        ?string $phaseId = null
    ): Collection {
        $application = \Modules\Application\Entities\Application::findOrFail($applicationId);

        // Obtener jurados disponibles desde JuryAssignmentService
        $availableEvaluators = $this->juryAssignmentService->getAvailableEvaluators(
            $application->job_profile_vacancy_id,
            $phaseId,
            $applicationId
        );

        return $availableEvaluators->map(function ($assignment) {
            return [
                'id' => $assignment->jury_member_id,
                'name' => $assignment->jury_member_name,
                'specialty' => $assignment->juryMember->specialty,
                'member_type' => $assignment->member_type->label(),
                'role' => $assignment->role_in_jury?->label(),
                'current_workload' => $assignment->current_evaluations,
                'max_capacity' => $assignment->max_evaluations,
                'workload_percentage' => $assignment->workload_percentage,
            ];
        });
    }

    /**
     * Asignación automática inteligente
     * ACTUALIZADO: Usa WorkloadBalancerService
     */
    public function autoAssign(string $applicationId, string $phaseId): EvaluatorAssignment
    {
        $application = \Modules\Application\Entities\Application::findOrFail($applicationId);

        // Sugerir mejor jurado disponible
        $bestJury = $this->juryAssignmentService->balanceWorkload($application->job_profile_vacancy_id);

        if (!$bestJury) {
            throw new \Exception('No hay jurados disponibles para asignar');
        }

        return $this->assignEvaluator([
            'application_id' => $applicationId,
            'evaluator_id' => $bestJury['assignment_id'],
            'phase_id' => $phaseId,
        ]);
    }

    /**
     * Remover asignación de evaluador
     * ACTUALIZADO: Decrementa carga del jurado
     */
    public function removeAssignment(string $assignmentId): bool
    {
        $assignment = EvaluatorAssignment::findOrFail($assignmentId);

        // Buscar jury assignment
        $application = $assignment->application;
        $juryAssignment = \Modules\Jury\Entities\JuryAssignment::where('jury_member_id', $assignment->evaluator_id)
            ->where('job_posting_id', $application->job_profile_vacancy_id)
            ->first();

        // Decrementar carga
        if ($juryAssignment) {
            $juryAssignment->decrementWorkload(1);
        }

        return $assignment->delete();
    }
}
