<?php

namespace Modules\Evaluation\Services;

use Illuminate\Support\Facades\DB;
use Modules\Evaluation\Entities\{Evaluation, EvaluationDetail, EvaluationHistory};
use Modules\Evaluation\Enums\EvaluationStatusEnum;
use Modules\Evaluation\Exceptions\EvaluationException;

class EvaluationService
{
    /**
     * Crear una nueva evaluación
     */
    public function createEvaluation(array $data): Evaluation
    {
        return DB::transaction(function () use ($data) {
            $evaluation = Evaluation::create([
                'application_id' => $data['application_id'],
                'evaluator_id' => $data['evaluator_id'],
                'phase_id' => $data['phase_id'],
                'job_posting_id' => $data['job_posting_id'],
                'status' => EvaluationStatusEnum::ASSIGNED,
                'deadline_at' => $data['deadline_at'] ?? null,
                'is_anonymous' => $data['is_anonymous'] ?? false,
                'is_collaborative' => $data['is_collaborative'] ?? false,
            ]);

            // Registrar en historial
            EvaluationHistory::logChange(
                $evaluation->id,
                auth()->id(),
                'CREATED',
                'Evaluación creada y asignada'
            );

            return $evaluation->fresh('details', 'phase', 'evaluator');
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
            EvaluationHistory::logChange(
                $evaluation->id,
                auth()->id(),
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
     */
    public function saveEvaluationDetail(Evaluation $evaluation, array $detailData): EvaluationDetail
    {
        if (!$evaluation->canEdit()) {
            throw new EvaluationException('Esta evaluación ya no puede ser editada');
        }

        return DB::transaction(function () use ($evaluation, $detailData) {
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
                EvaluationHistory::logChange(
                    $evaluation->id,
                    auth()->id(),
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

        // Validar que todos los criterios estén calificados
        $this->validateEvaluationComplete($evaluation);

        return DB::transaction(function () use ($evaluation) {
            $evaluation->submit();
            $evaluation->updateScores();

            // Marcar asignación como completada
            if ($assignment = $evaluation->evaluator->assignments()
                ->where('application_id', $evaluation->application_id)
                ->where('phase_id', $evaluation->phase_id)
                ->first()
            ) {
                $assignment->markAsCompleted();
            }

            // Registrar en historial
            EvaluationHistory::logChange(
                $evaluation->id,
                auth()->id(),
                'SUBMITTED',
                'Evaluación enviada y finalizada'
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
            EvaluationHistory::logChange(
                $evaluation->id,
                auth()->id(),
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
     * Eliminar evaluación
     */
    public function deleteEvaluation(Evaluation $evaluation): bool
    {
        return DB::transaction(function () use ($evaluation) {
            // Registrar en historial antes de eliminar
            EvaluationHistory::logChange(
                $evaluation->id,
                auth()->id(),
                'CANCELLED',
                'Evaluación eliminada'
            );

            return $evaluation->delete();
        });
    }

    /**
     * Obtener evaluaciones de un evaluador
     */
    public function getEvaluatorEvaluations(string $evaluatorId, array $filters = [])
    {
        $query = Evaluation::with(['application', 'phase', 'jobPosting'])
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

        if (isset($filters['completed_only']) && $filters['completed_only']) {
            $query->completed();
        }

        return $query->orderBy('deadline_at', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Validar que la evaluación esté completa
     */
    protected function validateEvaluationComplete(Evaluation $evaluation): void
    {
        // Obtener criterios requeridos para esta fase
        $requiredCriteria = \Modules\Evaluation\Entities\EvaluationCriterion::active()
            ->byPhase($evaluation->phase_id)
            ->byJobPosting($evaluation->job_posting_id)
            ->get();

        // Verificar que todos tengan calificación
        foreach ($requiredCriteria as $criterion) {
            $detail = $evaluation->details()
                ->where('criterion_id', $criterion->id)
                ->first();

            if (!$detail) {
                throw new EvaluationException(
                    "Falta calificar el criterio: {$criterion->name}"
                );
            }

            // Validar puntaje
            if (!$criterion->validateScore($detail->score)) {
                throw new EvaluationException(
                    "El puntaje del criterio '{$criterion->name}' debe estar entre {$criterion->min_score} y {$criterion->max_score}"
                );
            }

            // Validar comentario requerido
            if ($criterion->requiresComment() && empty($detail->comments)) {
                throw new EvaluationException(
                    "El criterio '{$criterion->name}' requiere comentarios"
                );
            }

            // Validar evidencia requerida
            if ($criterion->requiresEvidence() && empty($detail->evidence)) {
                throw new EvaluationException(
                    "El criterio '{$criterion->name}' requiere evidencia"
                );
            }
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
}