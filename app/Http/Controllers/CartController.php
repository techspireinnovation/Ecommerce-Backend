<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreRequest;
use App\Http\Requests\Cart\UpdateRequest;
use App\Http\Resources\CartResource;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Services\PaginationService;

class CartController extends Controller
{
    private CartRepositoryInterface $cartRepository;
    private PaginationService $paginationService;


    public function __construct(CartRepositoryInterface $cartRepository, PaginationService $paginationService)
    {
        $this->cartRepository = $cartRepository;
        $this->paginationService = $paginationService;
    }

    public function index()
    {
        $carts = $this->cartRepository->all();
        $pagination = $this->paginationService->format($carts);

        return response()->json([
            'success' => true,
            'data' => CartResource::collection($carts),
            ...$pagination
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
