<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductActiveResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'product_id' => $this->id,
            'product_name' => $this->name,

            'variants' => ProductVariantResource::collection(
                $this->whenLoaded('variants')
            ),
        ];
    }
}
