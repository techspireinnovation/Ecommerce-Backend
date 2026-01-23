<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'brand_id' => $this->brand_id,
            'brand_name' => $this->brand?->name,
            'subcategory_id' => $this->subcategory_id,
            'subcategory_name' => $this->subcategory?->name,
            'summary' => $this->summary,
            'overview' => $this->overview,
            'price' => $this->price,
            'discount_percentage' => $this->discount_percentage,

            'status' => match ($this->status) {
                0 => 'active',
                1 => 'inactive',
                3 => 'low stock',
                4 => 'sold out',
                default => 'unknown',
            },


            'seo_title' => $this->seo?->seo_title,
            'seo_description' => $this->seo?->seo_description,
            'seo_keywords' => $this->seo?->seo_keywords ?? [],
            'seo_image_url' => $this->seo?->seo_image ? asset('storage/' . $this->seo?->seo_image) : null,

            /* ======================
             | Tags / Highlights / Policies
             ====================== */
            'tags' => $this->tags ?? [],
            'highlights' => $this->highlights ?? [],
            'policies' => $this->policies ?? [],

            /* ======================
             | Specifications
             ====================== */
            'specifications' => $this->specifications ?? [],

            /* ======================
             | Variants
             ====================== */
            'variants' => $this->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color' => $variant->color,
                    'images' => collect($variant->images)->map(
                        fn($img) => asset('storage/' . $img)
                    )->toArray(),

                    'storages' => $variant->storages->map(function ($storage) {
                        return [
                            'id' => $storage->id,
                            'storage' => $storage->storage,
                            'sku' => $storage->sku,
                            'quantity' => $storage->quantity,
                            'low_stock_threshold' => $storage->low_stock_threshold,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
}
