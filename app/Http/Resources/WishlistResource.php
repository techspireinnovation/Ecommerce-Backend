<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\StockService;

class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        $product = $this->product;
        $variantStorage = $this->variantStorage;
        $variant = $variantStorage?->variant;
        $category = $product?->subcategory?->category;

        /** @var StockService $stockService */
        $stockService = app(StockService::class);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,

            'product_name' => $product?->name,
            'price' => $product?->price,
            'discount_percentage' => $product?->discount_percentage,

            'brand_name' => $product?->brand?->name,
            'category_name' => $category?->name,
            'subcategory_name' => $product?->subcategory?->name,

            'product_variant_storage' => $variantStorage ? [
                'id' => $variantStorage->id,
                'color' => $variant?->color,
                'storage' => $variantStorage->storage,

                'available_quantity' => $stockService
                    ->getAvailableQuantity($variantStorage->id),

                'images' => $variant
                    ? collect($variant->images)
                        ->map(fn($img) => asset('storage/' . $img))
                        ->values()
                        ->toArray()
                    : [],
            ] : null,
            'status' => $this->status ? 'moved_to_cart' : 'active',
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
