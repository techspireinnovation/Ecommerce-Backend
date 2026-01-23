<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShippingMethodResource extends JsonResource
{
    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'delivery_type' => $this->delivery_type,
            'delivery_type_name' => $this->delivery_type == 1 ? 'Inside Valley' : 'Outside Valley',
            'charge_amount' => $this->charge_amount,
            'free_delivery_min_amount' => $this->free_delivery_min_amount,
            'status' => $this->status ? 'inactive' : 'active',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
