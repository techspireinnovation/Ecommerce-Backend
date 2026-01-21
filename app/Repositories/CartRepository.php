<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\Product;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CartRepository implements CartRepositoryInterface
{
    public function all()
    {
        return Cart::with([
            'product',
            'productVariantStorage',
            'user'
        ])->where('status', 0)->get();
    }

    public function find(int $id)
    {
        return Cart::with([
            'product',
            'productVariantStorage',
            'user'
        ])->where('status', 0)->findOrFail($id);
    }





    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $cart = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $data['product_id'],
                'product_variant_storage_id' => $data['product_variant_storage_id'],
                'quantity' => $data['quantity'],
                'status' => 0,
            ]);

            return Cart::with(['product', 'productVariantStorage'])
                ->find($cart->id);
        });
    }

    public function toggle(int $id)
{
    return DB::transaction(function () use ($id) {
        $cart = Cart::findOrFail($id);

        $cart->update(['status' => 2]);
        app(\App\Repositories\WishlistRepository::class)->store([
            'product_id' => $cart->product_id,
            'product_variant_storage_id' => $cart->product_variant_storage_id,
        ]);
        return $cart->load(['product', 'productVariantStorage', 'user']);
    });
}


    public function delete(int $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete($id);
    }
}
