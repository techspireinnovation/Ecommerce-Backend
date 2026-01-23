<?php

namespace App\Repositories;

use App\Models\Deal;
use App\Models\DealProduct;
use App\Repositories\Interfaces\DealRepositoryInterface;
use App\Services\ImageService;
use App\Services\SoftDeletePivotSyncService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DealRepository implements DealRepositoryInterface
{
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function all()
    {
        return Deal::with([
            'products.brand',
            'products.subcategory',
            'products.variants.storages',
        ])->get();
    }

    public function find(int $id)
    {
        return Deal::with([
            'products.brand',
            'products.subcategory',
            'products.variants.storages',
        ])->findOrFail($id);
    }


    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->store($data['image'], 'deals');
            }

            $deal = Deal::create(Arr::only($data, [
                'title',
                'description',
                'image',
                'type',
                'amount',
                'start_date',
                'end_date',
                'status'
            ]));

            if (!empty($data['product_ids'])) {
                foreach ($data['product_ids'] as $productId) {
                    DealProduct::create([
                        'deal_id' => $deal->id,
                        'product_id' => $productId,
                    ]);
                }
            }

            return $deal->load('products');
        });
    }


    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $deal = Deal::findOrFail($id);

            // Replace image if provided
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->replace(
                    $deal->image,
                    $data['image'],
                    'deals'
                );
            }

            // Update main deal info
            $deal->update(Arr::only($data, [
                'title',
                'description',
                'image',
                'type',
                'amount',
                'start_date',
                'end_date',
                'status'
            ]));

            // Sync products using SoftDeletePivotSyncService
            if (!empty($data['product_ids'])) {
                app(SoftDeletePivotSyncService::class)->sync(
                    pivotModel: DealProduct::class,
                    parentId: $deal->id,
                    parentKey: 'deal_id',
                    relatedKey: 'product_id',
                    newIds: $data['product_ids']
                );
            }

            return $deal->load('products');
        });
    }



    public function delete(int $id)
    {
        $deal = Deal::findOrFail($id);

        $this->imageService->delete($deal->image);

        foreach ($deal->products as $product) {
            $product->pivot->delete();
        }

        $deal->delete($id);
    }


}
