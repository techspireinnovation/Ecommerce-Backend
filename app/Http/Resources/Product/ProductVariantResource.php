<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'variant_id' => $this->id,
            'color' => $this->color,

            'storages' => ProductVariantStorageResource::collection(
                $this->whenLoaded('storages')
            ),
        ];
    }
}
