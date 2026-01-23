<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSeoResource extends JsonResource
{
    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'seo_title' => $this->seo?->seo_title,
            'seo_description' => $this->seo?->seo_description,
            'seo_keywords' => $this->seo?->seo_keywords ?? [],
            'seo_image_url' => $this->seo?->seo_image ? asset('storage/' . $this->seo?->seo_image) : null,

        ];
    }
}
