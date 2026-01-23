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
    public function setDefaultPerPage(int $perPage): void
    {
        $this->defaultPerPage = $perPage;
    }
    public function find(int $id)
    {
        return ShippingMethod::query()->whereNull('deleted_at')->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Make sure status is integer
            $data['status'] = isset($data['status']) ? (int) $data['status'] : 0;

            // Create the new record first
            $newRecord = ShippingMethod::create(Arr::only($data, [
                'delivery_type',
                'charge_amount',
                'free_delivery_min_amount',
                'status'
            ]));

            // If new record is active (status 0), deactivate all other active records with same delivery_type
            if ($newRecord->status === 0) {
                ShippingMethod::query()
                    ->where('delivery_type', $newRecord->delivery_type)
                    ->where('status', 0)
                    ->where('id', '!=', $newRecord->id)
                    ->whereNull('deleted_at')
                    ->update(['status' => 1]);
            }

            return $newRecord;
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $method = ShippingMethod::findOrFail($id);

            if (isset($data['status'])) {
                $data['status'] = (int) $data['status'];
            }

            if (isset($data['status']) && $data['status'] === 0) {
                ShippingMethod::query()
                    ->where('delivery_type', $method->delivery_type)
                    ->where('status', 0)
                    ->where('id', '!=', $id)
                    ->whereNull('deleted_at')
                    ->update(['status' => 1]);
            }

            $method->update(Arr::only($data, [
                'delivery_type',
                'charge_amount',
                'free_delivery_min_amount',
                'status'
            ]));

            return $method;
        });
    }

    public function delete(int $id)
    {
        $method = ShippingMethod::findOrFail($id);
        $method->delete($id);
    }


}
