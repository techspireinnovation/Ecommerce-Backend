<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ShippingMethod;

class ShippingMethodResource extends JsonResource
{
    protected string $mode;

    public function __construct($resource, $mode = 'index')
    {
        parent::__construct($resource);
        $this->mode = $mode; // 'index' or 'show'
    }

    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }

        // Dynamically calculate weight_from
        $previous = ShippingMethod::query()
            ->where('delivery_type', $this->delivery_type)
            ->where('weight_to', '<', $this->weight_to)
            ->whereNull('deleted_at')
            ->orderByDesc('weight_to')
            ->first();

        $weightFrom = $previous ? $previous->weight_to : 0.0;

        if ($this->mode === 'show') {
            // Raw values for show
            return [
                'id' => $this->id,
                'delivery_type' => $this->delivery_type,
                'weight_from' => $weightFrom,
                'weight_to' => (float) $this->weight_to,
                'charge' => (float) $this->charge,
                'free_shipping_threshold' => $this->free_shipping_threshold,
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            ];
        }

        // Formatted for index display
        return [
            'id' => $this->id,
            'delivery_type' => $this->delivery_type,
            'delivery_type_name' => $this->delivery_type == 1 ? 'Inside Valley' : 'Outside Valley',
            'weight_from' => $weightFrom . ' kg',
            'weight_to' => (float) $this->weight_to . ' kg',
            'charge' => 'Rs ' . (float) $this->charge,
            'free_shipping_threshold' => $this->free_shipping_threshold !== null ? 'Rs ' . (float) $this->free_shipping_threshold : null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
