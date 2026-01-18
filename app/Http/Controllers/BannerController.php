<?php

namespace App\Http\Controllers;

use App\Http\Requests\Banner\StoreRequest;
use App\Http\Requests\Banner\UpdateRequest;
use App\Http\Resources\BannerResource;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    private BannerRepositoryInterface $bannerRepository;

    public function __construct(BannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => BannerResource::collection($this->bannerRepository->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new BannerResource($this->bannerRepository->find($id)),
        ]);
    }

    public function store(StoreRequest $request): JsonResponse
    {
       $this->bannerRepository->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully',
        ]);
    }

    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $banner = $this->bannerRepository->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => new BannerResource($banner),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bannerRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully',
        ]);
    }


}
