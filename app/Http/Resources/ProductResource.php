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
        $category = $this->subcategory?->category;


        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand_id' => $this->brand_id,
            'brand_name' => $this->brand?->name,
            'category_id' => $category?->id,
            'category_name' => $category?->name,
            'subcategory_id' => $this->subcategory_id,
            'subcategory_name' => $this->subcategory?->name,
            'summary' => $this->summary,
            'overview' => $this->overview,
            'price' => $this->price,
            'discount_percentage' => $this->discount_percentage !== null
                ? (float) $this->discount_percentage
                : null,

            'weight' => (float) $this->weight,
            'weight_type' => (int) $this->weight_type,
            'weight_type_label' => match ((int) $this->weight_type) {
                1 => 'gram',
                2 => 'kilogram',
                default => 'unknown',
            },
            'weight_display' => match ((int) $this->weight_type) {
                1 => ($this->weight * 1000) . ' g',
                2 => $this->weight . ' kg',
                default => null,
            },

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


            'tags' => $this->tags ?? [],
            'highlights' => $this->highlights ?? [],
            'policies' => $this->policies ?? [],


            'specifications' => $this->specifications ?? [],


            'variants' => $this->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color' => $variant->color,
                    'images' => $variant->images()->pluck('image')->map(
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

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
