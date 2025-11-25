<?php

namespace Modules\Configuration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->parsed_value,
            'raw_value' => $this->when($request->user()?->can('configuration.view_raw'), $this->value),
            'default_value' => $this->default_value,
            'value_type' => [
                'value' => $this->value_type->value,
                'label' => $this->value_type->label(),
            ],
            'input_type' => [
                'value' => $this->input_type->value,
                'label' => $this->input_type->label(),
            ],
            'description' => $this->description,
            'is_editable' => $this->is_editable,
            'is_system' => $this->is_system,
            'is_sensitive' => $this->is_sensitive,
            'validation_rules' => $this->validation_rules,
            'options' => $this->options,
            'min_value' => $this->min_value,
            'max_value' => $this->max_value,
            'help_text' => $this->help_text,
            'group' => new ConfigGroupResource($this->whenLoaded('group')),
            'group_id' => $this->group_id,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'updated_by' => $this->whenLoaded('updatedBy', fn() => [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->full_name,
            ]),
        ];
    }
}
