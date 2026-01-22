<?php

namespace Modules\Application\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EligibilityOverrideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,

            // Estado original y nuevo
            'original_status' => $this->original_status,
            'original_status_label' => $this->original_status_label,
            'original_reason' => $this->original_reason,
            'new_status' => $this->new_status,
            'new_status_label' => $this->new_status_label,

            // Decisión
            'decision' => $this->decision->value,
            'decision_label' => $this->decision->label(),
            'decision_color' => $this->decision->color(),

            // Resolución
            'resolution_type' => $this->resolution_type,
            'resolution_type_label' => $this->resolution_type_label,
            'resolution_summary' => $this->resolution_summary,
            'resolution_detail' => $this->resolution_detail,

            // Quién y cuándo
            'resolved_by' => $this->resolved_by,
            'resolved_at' => $this->resolved_at?->toISOString(),
            'resolved_at_formatted' => $this->resolved_at?->format('d/m/Y H:i'),

            // Relación con resolutor
            'resolver' => $this->whenLoaded('resolver', function () {
                return [
                    'id' => $this->resolver->id,
                    'name' => $this->resolver->name,
                    'email' => $this->resolver->email,
                    'full_name' => $this->resolver->full_name ?? $this->resolver->name,
                ];
            }),

            // Relación con aplicación
            'application' => $this->whenLoaded('application', function () {
                return [
                    'id' => $this->application->id,
                    'code' => $this->application->code,
                    'full_name' => $this->application->full_name,
                    'dni' => $this->application->dni,
                    'status' => $this->application->status->value,
                    'status_label' => $this->application->status->label(),
                ];
            }),

            // Metadata
            'metadata' => $this->metadata,

            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
