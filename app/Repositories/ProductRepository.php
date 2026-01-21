<?php

namespace App\Repositories;

use App\Http\Resources\Product\ProductActiveResource;
use App\Models\Product;
use App\Models\SeoDetail;
use App\Models\ProductVariant;
use App\Models\ProductVariantStorage;
use App\Models\ProductSpecification;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\ImageService;
use App\Services\SkuService;
use App\Services\SlugService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ProductRepository implements ProductRepositoryInterface
{
    protected ImageService $imageService;
    protected SlugService $slugService;
    protected SkuService $skuService;

    public function __construct(ImageService $imageService, SlugService $slugService, SkuService $skuService)
    {
        $this->imageService = $imageService;
        $this->slugService = $slugService;
        $this->skuService = $skuService;
    }


    public function all()
    {
        return Product::with(['seo', 'specifications', 'variants.storages'])->get();
    }

    public function find(int $id)
    {
        return Product::with(['seo', 'specifications', 'variants.storages'])->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $this->validateDuplicateSkus($data['variants']);

            /* ---------- Product ---------- */
            $data['slug'] = $this->slugService->createUniqueSlug($data['name'], Product::class);

            $product = Product::create([
                'name' => $data['name'],
                'brand_id' => $data['brand_id'],
                'subcategory_id' => $data['subcategory_id'],
                'slug' => $data['slug'],
                'summary' => $data['summary'],
                'overview' => $data['overview'],
                'price' => $data['price'],
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'status' => $data['status'] ?? 0,
                'highlights' => $data['highlights'],
                'policies' => $data['policies'],
                'tags' => $data['tags'] ?? [],
            ]);


            /* ---------- Specifications ---------- */
            $this->handleSpecifications($product, $data['specifications'] ?? []);

            /* ---------- Variants ---------- */
            $this->handleVariants($product, $data['variants']);

            return $product->load(['seo', 'specifications', 'variants.storages']);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $product = Product::findOrFail($id);

            $this->validateDuplicateSkus($data['variants']);

            /* ---------- Slug ---------- */
            $data['slug'] = (isset($data['name']) && $data['name'] !== $product->name)
                ? $this->slugService->createUniqueSlug($data['name'], Product::class)
                : $product->slug;

            $product->update([
                'name' => $data['name'],
                'brand_id' => $data['brand_id'],
                'subcategory_id' => $data['subcategory_id'],
                'slug' => $data['slug'],
                'summary' => $data['summary'],
                'overview' => $data['overview'],
                'price' => $data['price'],
                'discount_percentage' => $data['discount_percentage'] ?? null,
                'status' => $data['status'] ?? $product->status,
                'highlights' => $data['highlights'],
                'policies' => $data['policies'],
                'tags' => $data['tags'] ?? [],
            ]);

            /* ---------- Specifications ---------- */
            $product->specifications()->delete();
            $this->handleSpecifications($product, $data['specifications'] ?? []);


            $incomingVariantIds = collect($data['variants'])->pluck('id')->filter()->all();
            $variantsToDelete = $product->variants()->whereNotIn('id', $incomingVariantIds)->get();

            foreach ($variantsToDelete as $variant) {
                $variant->delete(); // this will automatically soft-delete related storages via ProductVariant model
            }

            $this->handleVariants($product, $data['variants']);

            return $product->load(['seo', 'specifications', 'variants.storages']);
        });
    }

    public function delete(int $id)
    {
        $product = Product::with('seo')->findOrFail($id);
        $this->imageService->delete($product->seo?->seo_image);
        $product->delete($id);
    }

    public function activeList()
    {
        $products = Product::query()
            ->where('status', 0)
            ->whereNull('deleted_at')
            ->with([
                'variants.storages' => fn($q) =>
                    $q->whereNull('deleted_at')
            ])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return ProductActiveResource::collection($products);
    }



    public function storeSeo(int $productId, array $seoData)
    {
        $product = Product::findOrFail($productId);

        $seoData = Arr::only($seoData, [
            'seo_title',
            'seo_description',
            'seo_keywords',
            'seo_image'
        ]);

        if (!empty($seoData['seo_image'])) {
            $seoData['seo_image'] = $product->seo
                ? $this->imageService->replace($product->seo->seo_image, $seoData['seo_image'], 'products')
                : $this->imageService->store($seoData['seo_image'], 'products');
        }

        $seoData['seo_title'] = $seoData['seo_title'] ?? '';
        $seoData['seo_description'] = $seoData['seo_description'] ?? '';
        $seoData['seo_keywords'] = $seoData['seo_keywords'] ?? [];

        if ($product->seo) {
            $product->seo->update($seoData);
        } else {
            $seoData['reference_id'] = $product->id;
            $seoData['type'] = 4;
            SeoDetail::create($seoData);
        }

        return $product->load('seo');
    }


    protected function handleSpecifications(Product $product, array $specifications)
    {
        foreach ($specifications as $specIndex => $specData) {
            $product->specifications()->create([
                'title' => $specData['title'],
                'sort_order' => $specData['sort_order'] ?? ($specIndex + 1),
                'specification_items' => collect($specData['items'] ?? [])
                    ->map(function ($item, $index) {
                        return [
                            'key' => $item['key'],
                            'value' => $item['value'],
                            'order' => $item['order'] ?? ($index + 1),
                        ];
                    })->toArray(),
            ]);
        }
    }

    protected function handleVariants(Product $product, array $variants)
    {
        foreach ($variants as $variantIndex => $variantData) {

            $uploadedImages = [];
            foreach ($variantData['images'] as $imageFile) {
                $uploadedImages[] = $this->imageService->store($imageFile, 'products/variants');
            }

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'color' => $variantData['color'],
                'sort_order' => $variantData['sort_order'] ?? ($variantIndex + 1),
                'images' => $uploadedImages,
            ]);

            $regenerate = false;

            if ($product->wasChanged('name') || $product->brand->wasChanged('name') || $variant->wasChanged('color')) {
                $regenerate = true;
            }

            $storages = $this->skuService->generateForStorages(
                array_map(fn($s) => array_merge($s, ['regenerate' => $regenerate]), $variantData['storages']),
                $product->brand_id,
                $product->name,
                $variantData['color']
            );

            foreach ($storages as $storageData) {
                ProductVariantStorage::create([
                    'product_variant_id' => $variant->id,
                    'storage' => $storageData['storage'],
                    'sku' => $storageData['sku'],
                    'quantity' => $storageData['quantity'],
                    'low_stock_threshold' => $storageData['low_stock_threshold'],
                ]);
            }
        }
    }



    /**
     * Validate duplicate SKUs across all variants before saving
     */
    protected function validateDuplicateSkus(array $variants)
    {
        $allSkus = collect($variants)
            ->flatMap(fn($v) => collect($v['storages'])->pluck('sku'))
            ->filter()
            ->all();

        $duplicates = array_unique(array_diff_assoc($allSkus, array_unique($allSkus)));

        if (!empty($duplicates)) {
            throw ValidationException::withMessages([
                'variants.*.storages.*.sku' => ['Duplicate SKUs detected: ' . implode(', ', $duplicates)]
            ]);
        }
    }
}
