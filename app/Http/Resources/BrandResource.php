<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class BrandResource extends JsonResource
{
    public function toArray($request): array
    {
        $route = $request->route();

        $isSingle = $route && $route->parameter('brand');

        $isAdmin = $route && $route->getPrefix() === 'admin';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,

            'image_url' => $this->image
                ? asset('storage/' . $this->image)
                : null,

            'status' => $this->status ? 'inactive' : 'active',

            $this->mergeWhen($isSingle || $isAdmin, [
                'seo_title' => $this->seo?->seo_title,
                'seo_description' => $this->seo?->seo_description,
                'seo_keywords' => $this->seo?->seo_keywords ?? [],
                'seo_image_url' => $this->seo?->seo_image
                    ? asset('storage/' . $this->seo->seo_image)
                    : null,
            ]),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
