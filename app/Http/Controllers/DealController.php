<?php

namespace App\Http\Controllers;

use App\Http\Requests\Deal\StoreRequest;
use App\Http\Requests\Deal\UpdateRequest;
use App\Http\Resources\DealResource;
use App\Repositories\Interfaces\DealRepositoryInterface;
use App\Services\PaginationService;

class DealController extends Controller
{
    private DealRepositoryInterface $dealRepository;
    private PaginationService $paginationService;


    public function __construct(DealRepositoryInterface $dealRepository, PaginationService $paginationService)
    {
        $this->dealRepository = $dealRepository;
        $this->paginationService = $paginationService;

    }

    public function index()
    {
        $deals = $this->dealRepository->all();
        $pagination = $this->paginationService->format($deals);

        return response()->json([
            'success' => true,
            'data' => DealResource::collection($deals),
            ...$pagination
        ]);
    }

    public function show(int $id)
    {
        $deal = $this->dealRepository->find($id);
        return response()->json([
            'success' => true,
            'data' => new DealResource($deal),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->dealRepository->store($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully',
        ]);
    }

    public function update(UpdateRequest $request, int $id)
    {
        $this->dealRepository->update($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully',
        ]);
    }

    public function destroy(int $id)
    {
        $this->dealRepository->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully',
        ]);
    }


}
