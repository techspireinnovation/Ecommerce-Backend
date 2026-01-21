<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request)
    {
        $product = $this->product;
        $category = $product?->subcategory?->category;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'price' => $this->product?->price,
            'discount_percentage' => $this->product?->discount_percentage,
            'brand_name' => $this->product->brand?->name,
            'category_name' => $category?->name,
            'subcategory_name' => $this->product->subcategory?->name,

            'product_variant_storage' => $this->productVariantStorage ? [
                'id' => $this->productVariantStorage->id,
                'color' => $this->productVariantStorage->variant?->color,
                'storage' => $this->productVariantStorage->storage,
                'quantity' => $this->productVariantStorage->quantity,
                'images' => $this->productVariantStorage->variant
                    ? collect($this->productVariantStorage->variant->images)
                        ->map(fn($img) => asset('storage/' . $img))
                        ->toArray()
                    : [],
            ] : null,

            'quantity' => $this->quantity,
            'status' => match ($this->status) {
                0 => 'active',
                1 => 'converted_to_order',
                2 => 'moved_to_wishlist',
                default => 'unknown',
            },

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
