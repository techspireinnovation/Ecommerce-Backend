<?php

namespace App\Repositories;

use App\Models\Wishlist;
use App\Repositories\Interfaces\WishlistRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WishlistRepository implements WishlistRepositoryInterface
{
    private array $with = ['product', 'variantStorage', 'user'];

    public function all()
    {
        return Wishlist::query()
            ->with($this->with)
            ->forAuthUser()
            ->get();
    }

    public function find(int $id)
    {
        return Wishlist::query()
            ->with($this->with)
            ->forAuthUser()
            ->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            return Wishlist::updateOrCreate(
                [
                    'product_variant_storage_id' => $data['product_variant_storage_id'],
                ],
                [
                    'product_id' => $data['product_id'],
                    'status' => 0,
                ]
            )->load($this->with);
        });
    }

    public function toggle(int $id)
    {
        return DB::transaction(function () use ($id) {

            $wishlist = Wishlist::query()
                ->forAuthUser()
                ->findOrFail($id);

            $cartRepo = app(\App\Repositories\CartRepository::class);

            $existingCartItem = $cartRepo->all()
                ->where('user_id', auth()->id())
                ->where('product_id', $wishlist->product_id)
                ->where('product_variant_storage_id', $wishlist->product_variant_storage_id)
                ->first();

            if (!$existingCartItem) {
                $cartRepo->store([
                    'product_id' => $wishlist->product_id,
                    'product_variant_storage_id' => $wishlist->product_variant_storage_id,
                    'quantity' => 1, // default quantity
                ]);
            }

            $wishlist->update(['status' => 1]);

            return $wishlist->load($this->with);
        });
    }




    public function delete(int $id): void
    {
        Wishlist::query()
            ->forAuthUser()
            ->findOrFail($id)
            ->delete($id);
    }
}
