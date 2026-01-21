<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreRequest;
use App\Http\Requests\Cart\UpdateRequest;
use App\Http\Resources\CartResource;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartController extends Controller
{
    private CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function index()
    {
        $carts = $this->cartRepository->all();

        return response()->json([
            'success' => true,
            'data' => CartResource::collection($carts),
        ]);
    }

    public function show(int $id)
    {
        $cart = $this->cartRepository->find($id);

        return response()->json([
            'success' => true,
            'data' => new CartResource($cart),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->cartRepository->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cart created successfully',
        ]);
    }

    public function toggleMoveToWish(int $id)
    {
        $this->cartRepository->toggle($id);

        return response()->json([
            'success' => true,
            'message' => 'Cart moved to wishlist successfully.',
        ]);
    }

    public function destroy(int $id)
    {
        $this->cartRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Cart deleted successfully',
        ]);
    }
}
