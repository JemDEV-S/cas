<?php

namespace Modules\Organization\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationalUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'type' => [
                'value' => $this->type,
                'label' => $this->type,
            ],
            'parent_id' => $this->parent_id,
            'level' => $this->level,
            'path' => $this->path,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'metadata' => $this->metadata,

            // Relaciones
            'parent' => $this->whenLoaded('parent', function () {
                return [
                    'id' => $this->parent->id,
                    'code' => $this->parent->code,
                    'name' => $this->parent->name,
                ];
            }),

            'children' => OrganizationalUnitResource::collection($this->whenLoaded('children')),

            'children_count' => $this->when(
                $this->relationLoaded('children'),
                fn() => $this->children->count()
            ),

            // Información adicional
            'has_children' => $this->hasChildren(),
            'is_root' => $this->isRoot(),
            'full_path' => $this->full_path,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
