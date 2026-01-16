<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductController extends Controller
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        $products = $this->productRepository->all();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    public function show(int $id)
    {
        $product = $this->productRepository->find($id);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->productRepository->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
        ]);
    }

    public function update(UpdateRequest $request, int $id)
    {
        $this->productRepository->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
        ]);
    }

    public function destroy(int $id)
    {
        $this->productRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    public function activeProducts()
    {
        $products = $this->productRepository->activeList();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
