<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MenuItem */
class MenuItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'url' => $this->resolvedUrl(),
            'target_blank' => (bool) $this->target_blank,
            'has_children' => $this->when(
                $this->relationLoaded('children'),
                fn () => $this->children->isNotEmpty()
            ),
            'children' => MenuItemResource::collection(
                $this->whenLoaded('children')
            ),
        ];
    }
}
