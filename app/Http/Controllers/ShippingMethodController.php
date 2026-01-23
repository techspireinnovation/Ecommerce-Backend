<?php
namespace App\Http\Controllers;

use App\Http\Requests\ShippingMethod\StoreRequest;
use App\Http\Requests\ShippingMethod\UpdateRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Repositories\Interfaces\ShippingMethodRepositoryInterface;

class ShippingMethodController extends Controller
{
    private ShippingMethodRepositoryInterface $shippingRepository;

    public function __construct(ShippingMethodRepositoryInterface $shippingRepository)
    {
        $this->shippingRepository = $shippingRepository;
    }

    public function index()
    {
        $methods = $this->shippingRepository->all();
        return response()->json([
            'success' => true,
            'data' => ShippingMethodResource::collection($methods),
        ]);
    }

    public function show(int $id)
    {
        $method = $this->shippingRepository->find($id);
        return response()->json([
            'success' => true,
            'data' => new ShippingMethodResource($method),
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
