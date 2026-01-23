<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request): array
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'type_name' => match ($this->type) {
                1 => 'home page',
                2 => 'hero page',
                3 => 'ads',
                4 => 'about page',
                default => 'unknown',
            },
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'status' => $this->status ? 'inactive' : 'active',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
