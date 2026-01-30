<?php

namespace Modules\Evaluation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Evaluation\Entities\{Evaluation, EvaluationDetail, EvaluationHistory, EvaluationCriterion};
use Modules\Evaluation\Enums\EvaluationStatusEnum;
use Modules\Evaluation\Exceptions\EvaluationException;
use Modules\Evaluation\Entities\EvaluatorAssignment;

class EvaluationService
{
    /**
     * Crear una nueva evaluación a partir de una asignación
     */
    public function createEvaluation(EvaluatorAssignment $assignment): Evaluation
    {
        return DB::transaction(function () use ($assignment) {
            $evaluation = Evaluation::create([
                'evaluator_assignment_id' => $assignment->id,
                'application_id' => $assignment->application_id,
                'evaluator_id' => $assignment->user_id,
                'phase_id' => $assignment->phase_id,
                'job_posting_id' => $assignment->job_posting_id,
                'status' => EvaluationStatusEnum::ASSIGNED,
                'deadline_at' => $assignment->deadline_at,
                'is_anonymous' => $assignment->metadata['is_anonymous'] ?? false,
                'is_collaborative' => $assignment->metadata['is_collaborative'] ?? false,
            ]);

            // Marcar asignación como en progreso
            if ($assignment->status === \Modules\Evaluation\Enums\AssignmentStatusEnum::PENDING) {
                $assignment->markAsInProgress();
            }

            // Registrar en historial
            $userId = auth()->id() ?? $assignment->user_id;

            EvaluationHistory::logChange(
                $evaluation->id,
                $userId,
                'CREATED',
                'Evaluación creada desde asignación'
            );

            return $evaluation->fresh('details', 'phase', 'evaluator', 'evaluatorAssignment');
        });
    }

    /**
     * Actualizar una evaluación (borrador)
     */
    public function updateEvaluation(Evaluation $evaluation, array $data): Evaluation
    {
        if (!$evaluation->canEdit()) {
            throw new EvaluationException('Esta evaluación ya no puede ser editada');
        }

        return DB::transaction(function () use ($evaluation, $data) {
            $oldValues = $evaluation->only(['general_comments', 'internal_notes', 'status']);

            $evaluation->update([
                'general_comments' => $data['general_comments'] ?? $evaluation->general_comments,
                'internal_notes' => $data['internal_notes'] ?? $evaluation->internal_notes,
                'status' => $data['status'] ?? $evaluation->status,
            ]);

            // Registrar en historial
            // Si no hay usuario autenticado, usar el evaluator_id
            $userId = auth()->id() ?? $evaluation->evaluator_id;

            EvaluationHistory::logChange(
                $evaluation->id,
                $userId,
                'UPDATED',
                'Evaluación actualizada',
                $oldValues,
                $evaluation->only(['general_comments', 'internal_notes', 'status'])
            );

            return $evaluation->fresh();
        });
    }

    /**
     * Guardar o actualizar detalle de evaluación (calificación por criterio)
     * O guardar descalificación si es el caso
     */
    public function saveEvaluationDetail(Evaluation $evaluation, array $detailData)
    {
        if (!$evaluation->canEdit()) {
            throw new EvaluationException('Esta evaluación ya no puede ser editada');
        }

        return DB::transaction(function () use ($evaluation, $detailData) {
            // Si es una descalificación
            if (isset($detailData['disqualified']) && $detailData['disqualified'] === true) {
                // Guardar en metadata y general_comments
                $metadata = $evaluation->metadata ?? [];
                $metadata['disqualified'] = true;
                $metadata['disqualification_type'] = $detailData['disqualification_type'] ?? null;

                $evaluation->update([
                    'metadata' => $metadata,
                    'general_comments' => $detailData['disqualification_reason'],
                    'status' => EvaluationStatusEnum::IN_PROGRESS,
                    'total_score' => 0,
                ]);

                // Registrar en historial
                $userId = auth()->id() ?? $evaluation->evaluator_id;
                EvaluationHistory::logChange(
                    $evaluation->id,
                    $userId,
                    'DISQUALIFIED',
                    'Postulante descalificado',
                    null,
                    ['reason' => $detailData['disqualification_reason']]
                );

                return $evaluation;
            }

            // Flujo normal para detalles de criterios
            $detail = $evaluation->details()
                ->where('criterion_id', $detailData['criterion_id'])
                ->first();

            if ($detail) {
                // Actualizar existente
                $oldScore = $detail->score;
                $detail->update([
                    'score' => $detailData['score'],
                    'comments' => $detailData['comments'] ?? $detail->comments,
                    'evidence' => $detailData['evidence'] ?? $detail->evidence,
                    'version' => $detail->version + 1,
                    'change_reason' => $detailData['change_reason'] ?? null,
                ]);

                // Registrar cambio
                $userId = auth()->id() ?? $evaluation->evaluator_id;

                EvaluationHistory::logChange(
                    $evaluation->id,
                    $userId,
                    'CRITERION_CHANGED',
                    "Criterio actualizado",
                    ['score' => $oldScore],
                    ['score' => $detail->score]
                );
            } else {
                // Crear nuevo
                $detail = $evaluation->details()->create([
                    'criterion_id' => $detailData['criterion_id'],
                    'score' => $detailData['score'],
                    'comments' => $detailData['comments'] ?? null,
                    'evidence' => $detailData['evidence'] ?? null,
                ]);
            }

            // Marcar evaluación como en progreso
            if ($evaluation->status === EvaluationStatusEnum::ASSIGNED) {
                $evaluation->update(['status' => EvaluationStatusEnum::IN_PROGRESS]);
            }

            return $detail->fresh('criterion');
        });
    }

    /**
     * Enviar evaluación (finalizar)
     */
    public function submitEvaluation(Evaluation $evaluation): Evaluation
    {
        if (!$evaluation->canEdit()) {
            throw new EvaluationException('Esta evaluación ya no puede ser enviada');
        }

        // Validar que todos los criterios estén calificados (o que tenga descalificación)
        $this->validateEvaluationComplete($evaluation);

        return DB::transaction(function () use ($evaluation) {
            $evaluation->submit();

            // Solo actualizar scores si no está descalificado
            $metadata = $evaluation->metadata ?? [];
            if (!isset($metadata['disqualified']) || $metadata['disqualified'] !== true) {
                $evaluation->updateScores();
            }

            // Marcar asignación como completada
            if ($evaluation->evaluatorAssignment) {
                $evaluation->evaluatorAssignment->markAsCompleted();
            }

            // Registrar en historial
            $userId = auth()->id() ?? $evaluation->evaluator_id;

            $message = isset($metadata['disqualified']) && $metadata['disqualified']
                ? 'Evaluación enviada - Postulante descalificado'
                : 'Evaluación enviada y finalizada';

            EvaluationHistory::logChange(
                $evaluation->id,
                $userId,
                'SUBMITTED',
                $message
            );

            // Disparar evento
            event(new \Modules\Evaluation\Events\EvaluationSubmitted($evaluation));

            return $evaluation->fresh();
        });
    }

    /**
     * Modificar evaluación ya enviada (solo administradores)
     */
    public function modifySubmittedEvaluation(
        Evaluation $evaluation,
        array $data,
        string $reason
    ): Evaluation {
        if (!$evaluation->isCompleted()) {
            throw new EvaluationException('Solo se pueden modificar evaluaciones ya enviadas');
        }

        return DB::transaction(function () use ($evaluation, $data, $reason) {
            $oldValues = [
                'total_score' => $evaluation->total_score,
                'details' => $evaluation->details->map(function ($detail) {
                    return [
                        'criterion_id' => $detail->criterion_id,
                        'score' => $detail->score,
                    ];
                })->toArray(),
            ];

            // Actualizar detalles si se proporcionan
            if (isset($data['details'])) {
                foreach ($data['details'] as $detailData) {
                    $this->saveEvaluationDetail($evaluation, $detailData);
                }
            }

            // Actualizar evaluación
            $evaluation->update([
                'status' => EvaluationStatusEnum::MODIFIED,
                'modified_by' => auth()->id(),
                'modified_at' => now(),
                'modification_reason' => $reason,
                'general_comments' => $data['general_comments'] ?? $evaluation->general_comments,
            ]);

            $evaluation->updateScores();

            // Registrar en historial
            // Si no hay usuario autenticado, usar el modified_by o evaluator_id
            $userId = auth()->id() ?? $evaluation->modified_by ?? $evaluation->evaluator_id;

            EvaluationHistory::logChange(
                $evaluation->id,
                $userId,
                'MODIFIED',
                'Evaluación modificada después de envío',
                $oldValues,
                [
                    'total_score' => $evaluation->total_score,
                    'details' => $evaluation->details->map(function ($detail) {
                        return [
                            'criterion_id' => $detail->criterion_id,
                            'score' => $detail->score,
                        ];
                    })->toArray(),
                ],
                $reason
            );

            // Disparar evento
            event(new \Modules\Evaluation\Events\EvaluationModified(
                $evaluation,
                $reason,
                auth()->id()
            ));

            return $evaluation->fresh();
        });
    }

    /**
     * Eliminar evaluación, sus detalles y resetear assignment
     */
    public function deleteEvaluation(Evaluation $evaluation): bool
    {
        return DB::transaction(function () use ($evaluation) {
            // Registrar en historial antes de eliminar
            $userId = auth()->id() ?? $evaluation->evaluator_id;

            EvaluationHistory::logChange(
                $evaluation->id,
                $userId,
                'CANCELLED',
                'Evaluación eliminada - El evaluador podrá volver a evaluar'
            );

            // Eliminar todos los detalles de evaluación
            $evaluation->details()->delete();

            // Resetear el assignment a PENDING si existe
            if ($evaluation->evaluatorAssignment) {
                $evaluation->evaluatorAssignment->update([
                    'status' => \Modules\Evaluation\Enums\AssignmentStatusEnum::PENDING,
                    'completed_at' => null,
                ]);
            }

            // Eliminar la evaluación
            return $evaluation->delete();
        });
    }

    /**
     * Obtener asignaciones de un evaluador (con sus evaluaciones si existen)
     */
    public function getEvaluatorAssignments(string $evaluatorId, array $filters = [])
    {
        $query = EvaluatorAssignment::with([
            'application.jobProfile.jobPosting',
            'application.jobProfile.requestingUnit',
            'application.jobProfile.positionCode',
            'phase',
            'jobPosting',
            'evaluation',
        ])->byEvaluator($evaluatorId);

        if (isset($filters['status'])) {
            $query->where('evaluator_assignments.status', $filters['status']);
        }

        if (isset($filters['phase_id'])) {
            $query->byPhase($filters['phase_id']);
        }

        if (isset($filters['job_posting_id'])) {
            $query->where('evaluator_assignments.job_posting_id', $filters['job_posting_id']);
        }

        if (isset($filters['requesting_unit_id'])) {
            $query->whereHas('application.jobProfile', function($q) use ($filters) {
                $q->where('requesting_unit_id', $filters['requesting_unit_id']);
            });
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('application', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        if (isset($filters['pending_only']) && $filters['pending_only']) {
            $query->whereIn('evaluator_assignments.status', [
                \Modules\Evaluation\Enums\AssignmentStatusEnum::PENDING,
                \Modules\Evaluation\Enums\AssignmentStatusEnum::IN_PROGRESS
            ]);
        }

        if (isset($filters['completed_only']) && $filters['completed_only']) {
            $query->where('evaluator_assignments.status', \Modules\Evaluation\Enums\AssignmentStatusEnum::COMPLETED);
        }

        // Ordenar por código de posición, luego por unidad orgánica, deadline y fecha de creación
        return $query->join('applications', 'evaluator_assignments.application_id', '=', 'applications.id')
            ->join('job_profiles', 'applications.job_profile_id', '=', 'job_profiles.id')
            ->join('organizational_units', 'job_profiles.requesting_unit_id', '=', 'organizational_units.id')
            ->leftJoin('position_codes', 'job_profiles.position_code_id', '=', 'position_codes.id')
            ->select('evaluator_assignments.*')
            ->orderBy('position_codes.code', 'asc')
            ->orderBy('organizational_units.name', 'asc')
            ->orderBy('evaluator_assignments.deadline_at', 'asc')
            ->orderBy('evaluator_assignments.created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Validar que la evaluación esté completa
     */
    protected function validateEvaluationComplete(Evaluation $evaluation): void
    {
        // Si está descalificado, no validar criterios
        $metadata = $evaluation->metadata ?? [];
        if (isset($metadata['disqualified']) && $metadata['disqualified'] === true) {
            // Solo validar que tenga el motivo de descalificación
            if (empty($evaluation->general_comments)) {
                throw new EvaluationException(
                    "Debe especificar el motivo de la descalificación"
                );
            }
            return;
        }

        // Obtener el position_code_id desde la postulación
        $positionCodeId = $evaluation->application
            ? $evaluation->application->jobProfile?->position_code_id
            : null;

        // Obtener criterios requeridos para esta fase
        $query = \Modules\Evaluation\Entities\EvaluationCriterion::active()
            ->byPhase($evaluation->phase_id)
            ->byJobPosting($evaluation->job_posting_id);

        // Filtrar por position_code si existe
        if ($positionCodeId) {
            $query->byPositionCode($positionCodeId);
        }

        $requiredCriteria = $query->get();

        // Verificar que todos tengan calificación
        foreach ($requiredCriteria as $criterion) {
            $detail = $evaluation->details()
                ->where('criterion_id', $criterion->id)
                ->first();

            // if (!$detail) {
            //     throw new EvaluationException(
            //         "Falta calificar el criterio: {$criterion->name}"
            //     );
            // }

            // // Validar puntaje
            // if (!$criterion->validateScore($detail->score)) {
            //     throw new EvaluationException(
            //         "El puntaje del criterio '{$criterion->name}' debe estar entre {$criterion->min_score} y {$criterion->max_score}"
            //     );
            // }

            // // Validar comentario requerido
            // if ($criterion->requiresComment() && empty($detail->comments)) {
            //     throw new EvaluationException(
            //         "El criterio '{$criterion->name}' requiere comentarios"
            //     );
            // }

            // Evidencia ahora es opcional - comentado la validación
            // if ($criterion->requiresEvidence() && empty($detail->evidence)) {
            //     throw new EvaluationException(
            //         "El criterio '{$criterion->name}' requiere evidencia"
            //     );
            // }
        }
    }

    /**
     * Obtener estadísticas de evaluaciones
     */
    public function getEvaluationStats(array $filters = []): array
    {
        $query = Evaluation::query();

        if (isset($filters['evaluator_id'])) {
            $query->byEvaluator($filters['evaluator_id']);
        }

        if (isset($filters['phase_id'])) {
            $query->byPhase($filters['phase_id']);
        }

        if (isset($filters['job_posting_id'])) {
            $query->where('job_posting_id', $filters['job_posting_id']);
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->pending()->count(),
            'completed' => (clone $query)->completed()->count(),
            'overdue' => (clone $query)->overdue()->count(),
            'average_score' => (clone $query)->completed()->avg('total_score') ?? 0,
        ];
    }

    /**
     * Actualizar un puntaje individual en modo bulk edit (para administradores)
     * Este método es similar a saveEvaluationDetail pero optimizado para edición masiva
     *
     * @param int $evaluationId
     * @param int $criterionId
     * @param float $score
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function bulkUpdateScore(int $evaluationId, int $criterionId, float $score): array
    {
        try {
            $evaluation = Evaluation::with(['details', 'application.jobProfile.positionCode'])
                ->findOrFail($evaluationId);

            // Validar que la evaluación esté en estado válido para edición bulk
            if (!in_array($evaluation->status->value, ['SUBMITTED', 'MODIFIED'])) {
                return [
                    'success' => false,
                    'message' => 'Solo se pueden editar evaluaciones completadas (SUBMITTED o MODIFIED)',
                    'data' => null,
                ];
            }

            $criterion = EvaluationCriterion::findOrFail($criterionId);

            // Validar rango de puntaje
            if (!$criterion->validateScore($score)) {
                return [
                    'success' => false,
                    'message' => "El puntaje debe estar entre {$criterion->min_score} y {$criterion->max_score}",
                    'data' => null,
                ];
            }

            return DB::transaction(function () use ($evaluation, $criterion, $score) {
                // Buscar o crear el detalle
                $detail = $evaluation->details()
                    ->where('criterion_id', $criterion->id)
                    ->first();

                $oldScore = $detail?->score;
                $isNewDetail = !$detail;

                if ($detail) {
                    // Actualizar existente
                    $detail->update([
                        'score' => $score,
                        'version' => $detail->version + 1,
                        'change_reason' => 'Actualización masiva por administrador',
                    ]);
                } else {
                    // Crear nuevo detalle
                    $detail = $evaluation->details()->create([
                        'criterion_id' => $criterion->id,
                        'score' => $score,
                        'change_reason' => 'Creado en edición masiva por administrador',
                    ]);
                }

                // Actualizar estado de la evaluación a MODIFIED si estaba SUBMITTED
                if ($evaluation->status->value === 'SUBMITTED') {
                    $evaluation->update([
                        'status' => EvaluationStatusEnum::MODIFIED,
                        'modified_by' => auth()->id(),
                        'modified_at' => now(),
                        'modification_reason' => 'Modificación masiva de puntajes',
                    ]);
                }

                // Registrar en historial
                $userId = auth()->id();
                $action = 'CRITERION_CHANGED'; // Siempre usamos CRITERION_CHANGED (es un valor válido del ENUM)
                $description = $isNewDetail
                    ? "Criterio '{$criterion->name}' agregado en edición masiva"
                    : "Criterio '{$criterion->name}' actualizado en edición masiva";

                EvaluationHistory::logChange(
                    $evaluation->id,
                    $userId,
                    $action,
                    $description,
                    ['score' => $oldScore],
                    ['score' => $score],
                    'Edición masiva por administrador'
                );

                // Refrescar para obtener los scores actualizados (se calculan automáticamente)
                $evaluation->refresh();

                // Actualizar el puntaje en la tabla de application según la fase
                $this->updateApplicationScore($evaluation);

                return [
                    'success' => true,
                    'message' => 'Puntaje actualizado correctamente',
                    'data' => [
                        'detail_id' => $detail->id,
                        'score' => $detail->score,
                        'weighted_score' => $detail->weighted_score,
                        'version' => $detail->version,
                        'total_score' => $evaluation->total_score,
                        'percentage' => $evaluation->percentage,
                    ],
                ];
            });

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Evaluación o criterio no encontrado',
                'data' => null,
            ];
        } catch (\Exception $e) {
            \Log::error('Error en bulkUpdateScore: ' . $e->getMessage(), [
                'evaluation_id' => $evaluationId,
                'criterion_id' => $criterionId,
                'score' => $score,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el puntaje: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Actualizar el puntaje en la tabla application según la fase de evaluación
     *
     * @param Evaluation $evaluation
     * @return void
     */
    protected function updateApplicationScore(Evaluation $evaluation): void
    {
        try {
            $application = $evaluation->application;
            if (!$application) {
                \Log::warning('No se encontró la application para la evaluación', [
                    'evaluation_id' => $evaluation->id,
                ]);
                return;
            }

            $phase = $evaluation->phase;
            if (!$phase) {
                \Log::warning('No se encontró la fase para la evaluación', [
                    'evaluation_id' => $evaluation->id,
                ]);
                return;
            }

            // Determinar qué campo actualizar según el código de la fase
            $scoreField = null;
            switch ($phase->code) {
                case 'PHASE_06_CV_EVALUATION':
                    $scoreField = 'curriculum_score';
                    break;
                case 'PHASE_08_INTERVIEW':
                    $scoreField = 'interview_score';
                    break;
                default:
                    // Si no es una fase de evaluación curricular o entrevista, no actualizamos
                    \Log::info('Fase no requiere actualización de puntaje en application', [
                        'phase_code' => $phase->code,
                        'evaluation_id' => $evaluation->id,
                    ]);
                    return;
            }

            // Actualizar el puntaje en la application
            $application->update([
                $scoreField => $evaluation->total_score,
            ]);

            \Log::info('Puntaje actualizado en application', [
                'application_id' => $application->id,
                'evaluation_id' => $evaluation->id,
                'phase_code' => $phase->code,
                'field' => $scoreField,
                'score' => $evaluation->total_score,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar puntaje en application: ' . $e->getMessage(), [
                'evaluation_id' => $evaluation->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
