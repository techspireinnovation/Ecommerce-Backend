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
        ])->get();
    }

    public function find(int $id)
    {
        return Cart::with([
            'product',
            'productVariantStorage',
            'user'
        ])->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $ids = [];

            foreach ($data['items'] as $item) {
                $cart = Cart::create([
                    'user_id' => auth()->id(),
                    'product_id' => $item['product_id'],
                    'product_variant_storage_id' => $item['product_variant_storage_id'],
                    'quantity' => $item['quantity'],
                    'status' => 0,
                ]);

                $ids[] = $cart->id;
            }

            return Cart::with(['product', 'productVariantStorage'])
                ->whereIn('id', $ids)
                ->get();
        });
    }



    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $cart = Cart::findOrFail($id);

            $cart->update(Arr::only($data, [
                'quantity',
                'status'
            ]));

            return $cart->load(['product', 'productVariantStorage']);
        });
    }

    public function delete(int $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();
    }
}
