<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class SubCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'status' => $this->status ? 'inactive' : 'active',
            'seo_title' => $this->seo?->seo_title,
            'seo_description' => $this->seo?->seo_description,
            'seo_keywords' => $this->seo?->seo_keywords ?? [],
            'seo_image_url' => $this->seo?->seo_image ? asset('storage/' . $this->seo?->seo_image) : null,
        ];
    }
}