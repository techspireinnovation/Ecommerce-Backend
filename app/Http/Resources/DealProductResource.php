<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DealProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,

            'variants' => $this->variants->map(function ($variant) {
                return [
                    'id'    => $variant->id,
                    'color' => $variant->color,

                    'images' => collect($variant->images)->map(
                        fn ($img) => asset('storage/' . $img)
                    )->toArray(),
                ];
            })->toArray(),
        ];
    }
}
