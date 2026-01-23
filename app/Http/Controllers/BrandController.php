<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\StoreRequest;
use App\Http\Requests\Brand\UpdateRequest;
use App\Http\Resources\BrandResource;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    private BrandRepositoryInterface $brandRepository;
    private PaginationService $paginationService;

    public function __construct(BrandRepositoryInterface $brandRepository, PaginationService $paginationService)
    {
        $this->brandRepository = $brandRepository;
        $this->paginationService = $paginationService;

    }

    public function index()
    {
        $brands = $this->brandRepository->all();

        $pagination = $this->paginationService->format($brands);

        return response()->json([
            'success' => true,
            'data' => BrandResource::collection($brands),
            ...$pagination
        ]);
    }



    public function show(int $id): JsonResponse
    {
        $brand = $this->brandRepository->find($id);

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand),
        ]);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        //$brand = $this->brandRepository->store($request->validated());
        $this->brandRepository->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            //'data' => new BrandResource($brand),
        ]);
    }

    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $this->brandRepository->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->brandRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }

    public function activeBrands(): JsonResponse
    {
        $brands = $this->brandRepository->activeList();
        return response()->json([
            'success' => true,
            'data' => $brands,

        ]);
    }
}
