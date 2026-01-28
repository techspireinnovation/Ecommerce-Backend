<?php
namespace App\Http\Controllers;

use App\Http\Requests\ShippingMethod\StoreRequest;
use App\Http\Requests\ShippingMethod\UpdateRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Repositories\Interfaces\ShippingMethodRepositoryInterface;
use App\Services\PaginationService;

class ShippingMethodController extends Controller
{
    private ShippingMethodRepositoryInterface $shippingRepository;
    private PaginationService $paginationService;

    public function __construct(ShippingMethodRepositoryInterface $shippingRepository, PaginationService $paginationService)
    {
        $this->shippingRepository = $shippingRepository;
        $this->paginationService = $paginationService;
    }

    // Index (formatted)
    public function index()
    {
        $methods = $this->shippingRepository->all();
        $pagination = $this->paginationService->format($methods);

        return response()->json([
            'success' => true,
            'data' => ShippingMethodResource::collection($methods), // default is index
            ...$pagination
        ]);
    }

    // Show (raw)
    public function show(int $id)
    {
        $method = $this->shippingRepository->find($id);
        return response()->json([
            'success' => true,
            'data' => new ShippingMethodResource($method, 'show'),
        ]);
    }


    public function store(StoreRequest $request)
    {
        $this->shippingRepository->store($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Shipping method created successfully',
        ]);
    }

    public function update(UpdateRequest $request, int $id)
    {
        $this->shippingRepository->update($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Shipping method updated successfully',
        ]);
    }

    public function destroy(int $id)
    {
        $this->shippingRepository->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Shipping method deleted successfully',
        ]);
    }

}
