<?php

namespace Modules\Evaluation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BulkEditEvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $application = $this->application;
        $jobProfile = $application?->jobProfile;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total_score' => $this->total_score,
            'max_possible_score' => $this->max_possible_score,
            'percentage' => $this->percentage,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'modified_at' => $this->modified_at?->format('Y-m-d H:i:s'),

            // Información del postulante
            'applicant' => [
                'id' => $application->id ?? null,
                'uuid' => $application->uuid ?? null,
                'full_name' => $application->full_name ?? 'N/A',
                'dni' => $application->dni ?? 'N/A',
                'position_code' => $jobProfile?->positionCode?->code ?? 'N/A',
                'position_name' => $jobProfile?->positionCode?->name ?? 'N/A',
            ],

            // Evaluador
            'evaluator' => [
                'id' => $this->evaluator_id,
                'name' => $this->evaluator?->name ?? 'N/A',
            ],

            // Detalles de criterios (puntajes)
            'details' => $this->details->mapWithKeys(function ($detail) {
                return [
                    'criterion_' . $detail->criterion_id => [
                        'detail_id' => $detail->id,
                        'score' => $detail->score,
                        'weighted_score' => $detail->weighted_score,
                        'version' => $detail->version,
                        'comments' => $detail->comments,
                    ]
                ];
            }),

            // Metadata para frontend
            'can_edit' => in_array($this->status->value, ['SUBMITTED', 'MODIFIED']),
        ];
    }
}
