<?php

namespace Modules\Evaluation\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            
            // Relaciones básicas
            'application_id' => $this->application_id,
            'evaluator_id' => $this->evaluator_id,
            'phase_id' => $this->phase_id,
            'job_posting_id' => $this->job_posting_id,
            
            // Estado y puntajes
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_badge_class' => $this->status->badgeClass(),
            'total_score' => (float) $this->total_score,
            'max_possible_score' => (float) $this->max_possible_score,
            'percentage' => (float) $this->percentage,
            
            // Fechas
            'submitted_at' => $this->submitted_at?->toISOString(),
            'deadline_at' => $this->deadline_at?->toISOString(),
            'modified_at' => $this->modified_at?->toISOString(),
            
            // Configuración
            'is_anonymous' => $this->is_anonymous,
            'is_collaborative' => $this->is_collaborative,
            
            // Comentarios
            'general_comments' => $this->general_comments,
            'internal_notes' => $this->when(
                $request->user()?->hasAnyRole(['Administrador General', 'Administrador de RRHH']),
                $this->internal_notes
            ),
            
            // Modificación (solo si aplica)
            'modified_by' => $this->modified_by,
            'modification_reason' => $this->modification_reason,
            
            // Estados calculados
            'can_edit' => $this->canEdit(),
            'is_completed' => $this->isCompleted(),
            'is_overdue' => $this->isOverdue(),
            
            // Relaciones (cuando están cargadas)
            'evaluator' => $this->whenLoaded('evaluator', function () {
                return [
                    'id' => $this->evaluator->id,
                    'name' => $this->evaluator->name,
                    'email' => $this->evaluator->email,
                ];
            }),
            
            'application' => $this->whenLoaded('application'),
            
            'phase' => $this->whenLoaded('phase', function () {
                return [
                    'id' => $this->phase->id,
                    'name' => $this->phase->name,
                    'code' => $this->phase->code,
                    'phase_number' => $this->phase->phase_number,
                ];
            }),
            
            'job_posting' => $this->whenLoaded('jobPosting', function () {
                return [
                    'id' => $this->jobPosting->id,
                    'title' => $this->jobPosting->title,
                    'code' => $this->jobPosting->code,
                ];
            }),
            
            'details' => $this->whenLoaded('details', function () {
                return $this->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'criterion_id' => $detail->criterion_id,
                        'criterion_name' => $detail->criterion->name ?? null,
                        'score' => (float) $detail->score,
                        'weighted_score' => (float) $detail->weighted_score,
                        'comments' => $detail->comments,
                        'evidence' => $detail->evidence,
                        'version' => $detail->version,
                    ];
                });
            }),
            
            // Metadata
            'metadata' => $this->metadata,
            
            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}