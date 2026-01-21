<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wishlist\StoreRequest;
use App\Http\Requests\Wishlist\UpdateRequest;
use App\Http\Resources\WishlistResource;
use App\Repositories\Interfaces\WishlistRepositoryInterface;

class WishlistController extends Controller
{
    private WishlistRepositoryInterface $wishlistRepository;

    public function __construct(WishlistRepositoryInterface $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    public function index()
    {
        $wishlists = $this->wishlistRepository->all();

        return response()->json([
            'success' => true,
            'data' => WishlistResource::collection($wishlists),
        ]);
    }

    public function show(int $id)
    {
        $wishlist = $this->wishlistRepository->find($id);

        return response()->json([
            'success' => true,
            'data' => new WishlistResource($wishlist),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->wishlistRepository->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist successfully.',
        ]);
    }

    public function toggleMoveToCart(int $id)
    {
        $wishlist = $this->wishlistRepository->toggle($id);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist item moved to cart successfully.',
        ]);
    }

    public function destroy(int $id)
    {
        $this->wishlistRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist removed successfully.',
        ]);
    }
}
