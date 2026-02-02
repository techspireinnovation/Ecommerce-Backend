<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\StoreSeoRequest;
use App\Http\Requests\Product\UpdateSeoRequest;

use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductSeoResource;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\PaginationService;

class ProductController extends Controller
{
    private ProductRepositoryInterface $productRepository;
    private PaginationService $paginationService;


    public function __construct(ProductRepositoryInterface $productRepository, PaginationService $paginationService)
    {
        $this->productRepository = $productRepository;
        $this->paginationService = $paginationService;

    }

    public function index()
    {
        $products = $this->productRepository->all();
        $pagination = $this->paginationService->format($products);


        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            ...$pagination
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
    public function showForSeo(int $id)
    {
        $product = $this->productRepository->find($id);

        return response()->json([
            'success' => true,
            'data' => new ProductSeoResource($product),
        ]);
    }


    public function store(StoreRequest $request)
    {
        $product = $this->productRepository->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'id' => $product->id,
        ]);
    }
    public function storeSeo(StoreSeoRequest $request, $id)
    {
        $data = $request->validated();
        $this->productRepository->storeSeo($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'SEO details saved successfully.',
        ]);
    }
    public function updateSeo(UpdateSeoRequest $request, $id)
    {
        $data = $request->validated();
        $this->productRepository->updateSeo($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'SEO details updated successfully.',
        ]);
    }
    public function update(UpdateRequest $request, int $id)
    {
        $product = $this->productRepository->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'id' => $product->id,
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
