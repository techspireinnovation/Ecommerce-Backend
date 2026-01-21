<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantStorageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'product_variant_storage_id' => $this->id,
            'storage' => $this->storage,
            'quantity' => $this->quantity,
        ];
    }
}
