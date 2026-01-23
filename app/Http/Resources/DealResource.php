<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            'image_url' => $this->image
                ? asset('storage/' . $this->image)
                : null,

            'type' => $this->type,
            'type_name' => match ($this->type) {
                1 => 'hot deals',
                2 => 'great deals',
                3 => 'flash sale',
                default => 'unknown',
            },

            $this->mergeWhen($this->type === 3, [
                'amount' => $this->amount,
                'start_date' => $this->start_date?->format('Y-m-d'),
                'end_date' => $this->end_date?->format('Y-m-d'),
            ]),

            'status' => $this->status ? 'inactive' : 'active',

            'products' => DealProductResource::collection($this->products),
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
}
