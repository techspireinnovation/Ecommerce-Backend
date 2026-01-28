<?php
namespace App\Repositories;

use App\Models\ShippingMethod;
use App\Repositories\Interfaces\ShippingMethodRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShippingMethodRepository implements ShippingMethodRepositoryInterface
{
    public function all()
    {
        return ShippingMethod::query()->whereNull('deleted_at')->paginate(20);
    }

    public function find(int $id)
    {
        return ShippingMethod::query()->whereNull('deleted_at')->findOrFail($id);
    }

    public function store(array $data): ShippingMethod
    {
        return DB::transaction(function () use ($data) {

            return ShippingMethod::create([
                'delivery_type' => $data['delivery_type'],
                'weight_to' => $data['weight_to'],
                'charge' => $data['charge'],
                'free_shipping_threshold' => $data['free_shipping_threshold'] ?? null,
            ]);
        });
    }

    public function update(int $id, array $data): ShippingMethod
    {
        return DB::transaction(function () use ($id, $data) {
            $method = ShippingMethod::findOrFail($id);


            // Update the current method
            $method->update([
                'delivery_type' => $data['delivery_type'], // add this line
                'weight_to' => $data['weight_to'],
                'charge' => $data['charge'],
                'free_shipping_threshold' => $data['free_shipping_threshold'] ?? $method->free_shipping_threshold,
                'status' => $data['status'] ?? $method->status,
            ]);



            return $method;
        });
    }

    public function delete(int $id)
    {
        $method = ShippingMethod::findOrFail($id);

        $latestMethod = ShippingMethod::query()
            ->where('delivery_type', $method->delivery_type)
            ->whereNull('deleted_at')
            ->orderByDesc('weight_to')
            ->first();

        if ($latestMethod->id !== $method->id) {
            throw new \Exception("Only the latest shipping method for this delivery type can be deleted.");
        }

        return DB::transaction(function () use ($method) {
            $method->delete();
            return true;
        });
    }

}