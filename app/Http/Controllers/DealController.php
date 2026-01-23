<?php

namespace App\Http\Controllers;

use App\Http\Requests\Deal\StoreRequest;
use App\Http\Requests\Deal\UpdateRequest;
use App\Http\Resources\DealResource;
use App\Repositories\Interfaces\DealRepositoryInterface;

class DealController extends Controller
{
    private DealRepositoryInterface $dealRepository;

    public function __construct(DealRepositoryInterface $dealRepository)
    {
        $this->dealRepository = $dealRepository;
    }

    public function index()
    {
        $deals = $this->dealRepository->all();
        return response()->json([
            'success' => true,
            'data' => DealResource::collection($deals),
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
