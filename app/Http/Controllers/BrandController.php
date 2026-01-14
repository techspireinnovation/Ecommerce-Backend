<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\StoreRequest;
use App\Http\Requests\Brand\UpdateRequest;
use App\Http\Resources\BrandResource;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    private BrandRepositoryInterface $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    // GET /api/brands
    public function index(): JsonResponse
    {
        $brands = $this->brandRepository->all();
        return response()->json([
            'success' => true,
            'data' => BrandResource::collection($brands),
        ]);
    }

    // GET /api/brands/{id}
    public function show(int $id): JsonResponse
    {
        $brand = $this->brandRepository->find($id);

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand),
        ]);
    }

    // POST /api/brands
    public function store(StoreRequest $request): JsonResponse
    {
        $brand = $this->brandRepository->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            //'data' => new BrandResource($brand),
        ]);
    }

    // PUT/PATCH /api/brands/{id}
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $brand = $this->brandRepository->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            //'data' => new BrandResource($brand),
        ]);
    }

    // DELETE /api/brands/{id}
    public function destroy(int $id): JsonResponse
    {
        $this->brandRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }
}
