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
        return Product::with(['seo', 'specifications', 'variants.storages'])->paginate(20);
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
                'weight_type' => $data['weight_type'],
                'weight' => $data['weight'],
                'status' => 1, // force status 1 initially
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
        $product->update(['status' => 0]);


        return $product->load('seo');
    }



    protected function handleVariants(Product $product, array $variants)
    {
        $incomingVariantIds = collect($variants)->pluck('id')->filter()->all();


        $product->variants()
            ->whereNotIn('id', $incomingVariantIds)
            ->delete();

        foreach ($variants as $variantIndex => $variantData) {

            if (!empty($variantData['id'])) {
                $variant = ProductVariant::withTrashed()
                    ->where('id', $variantData['id'])
                    ->where('product_id', $product->id)
                    ->firstOrFail();

                if ($variant->trashed()) {
                    $variant->restore();
                }
            } else {
                $variant = new ProductVariant([
                    'product_id' => $product->id,
                ]);
            }


            $variant->fill([
                'color' => $variantData['color'],
                'sort_order' => $variantIndex,
            ]);

            if ($variant->isDirty() || !$variant->exists) {
                $variant->save();
            }


            $images = $variantData['images'] ?? [];

            $hasExistingImages = $variant->exists && $variant->images()->count() > 0;

            $hasNewUploads = collect($images)->contains(fn($img) => $img instanceof \Illuminate\Http\UploadedFile);

            $shouldUpdateImages = !$hasNewUploads && $hasExistingImages;

            if ($shouldUpdateImages) {
                $existingImages = $variant->images()->pluck('image')->toArray();

                $newImages = collect($images)
                    ->filter(fn($img) => is_string($img))
                    ->map(function ($img) {
                        if (str_starts_with($img, url('/storage') . '/')) {
                            return str_replace(url('/storage') . '/', '', $img);
                        }
                        return $img;
                    })
                    ->toArray();

                $imagesChanged = count($existingImages) !== count($newImages) ||
                    !empty(array_diff($existingImages, $newImages));

                if ($imagesChanged) {
                    $variant->images()->delete();
                    $shouldUpdateImages = true;
                } else {
                    $shouldUpdateImages = false;
                }
            }

            // Process images based on the scenario
            if ($hasNewUploads) {
                // Case 1: Has new uploaded files
                if ($hasExistingImages) {
                    $variant->images()->delete();
                }

                foreach ($images as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $path = $this->imageService->store($image, 'products/variants');
                        $variant->images()->create(['image' => $path]);
                    } else if (is_string($image)) {
                        // Handle mixed case (file + URL in same array)
                        $relativePath = $this->normalizeImagePath($image);
                        $variant->images()->create(['image' => $relativePath]);
                    }
                }
            } else if ($shouldUpdateImages) {
                // Case 2: Existing variant, no new uploads, but images changed
                foreach ($images as $image) {
                    if (is_string($image)) {
                        $relativePath = $this->normalizeImagePath($image);
                        $variant->images()->create(['image' => $relativePath]);
                    }
                }
            } else if (!$variant->exists || $variant->wasRecentlyCreated) {
                // Case 3: New variant with URLs only
                foreach ($images as $image) {
                    if (is_string($image)) {
                        $relativePath = $this->normalizeImagePath($image);
                        $variant->images()->create(['image' => $relativePath]);
                    }
                }
            }
            // Case 4: Existing variant, no new uploads, no changes - do nothing

            /** ------------------------------
             * Handle Variant Storages
             * ----------------------------- */
            $incomingStorages = collect($variantData['storages'] ?? [])
                ->pluck('storage')
                ->toArray();

            // Soft-delete removed storages
            $variant->storages()
                ->whereNotIn('storage', $incomingStorages)
                ->delete();

            // Determine if SKU needs regeneration
            $regenerateSku =
                $product->wasChanged('name') ||
                ($product->brand && $product->brand->wasChanged('name')) ||
                $variant->wasChanged('color');

            // Generate storages with SKUs
            $storages = $this->skuService->generateForStorages(
                array_map(fn($s) => array_merge($s, ['regenerate' => $regenerateSku]), $variantData['storages'] ?? []),
                $product->brand_id,
                $product->name,
                $variant->color
            );

            foreach ($storages as $storageData) {
                $storage = ProductVariantStorage::withTrashed()->firstOrNew([
                    'product_variant_id' => $variant->id,
                    'storage' => $storageData['storage'],
                ]);

                if ($storage->trashed()) {
                    $storage->restore();
                }

                $storage->fill([
                    'sku' => $storageData['sku'],
                    'quantity' => $storageData['quantity'],
                    'low_stock_threshold' => $storageData['low_stock_threshold'],
                ]);

                if ($storage->isDirty() || !$storage->exists) {
                    $storage->save();
                }
            }
        }
    }


    private function normalizeImagePath(string $image): string
    {
        // If it's already a relative path (starts with 'products/'), return as-is
        if (str_starts_with($image, 'products/')) {
            return $image;
        }

        // If it's a full URL, extract the relative path
        $storageUrl = url('/storage') . '/';
        if (str_starts_with($image, $storageUrl)) {
            return str_replace($storageUrl, '', $image);
        }

        // If it's a full URL without the storage prefix (from previous duplication issue)
        $baseUrl = url('/') . '/';
        if (str_starts_with($image, $baseUrl . 'storage/')) {
            return str_replace($baseUrl . 'storage/', '', $image);
        }

        // Return as-is (could be a relative path already)
        return $image;
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
                'weight_type' => $data['weight_type'],
                'weight' => $data['weight'],
                'status' => $data['status'] ?? $product->status,
                'highlights' => $data['highlights'],
                'policies' => $data['policies'],
                'tags' => $data['tags'] ?? [],
            ]);

            /* ---------- Specifications ---------- */
            $this->handleSpecifications($product, $data['specifications'] ?? []);



            $incomingVariantIds = collect($data['variants'])->pluck('id')->filter()->all();
            $variantsToDelete = $product->variants()->whereNotIn('id', $incomingVariantIds)->get();

            foreach ($variantsToDelete as $variant) {
                $variant->delete(); // this will automatically soft-delete related storages via ProductVariant model
            }

            $this->handleVariants($product, $data['variants']);

            return $product->load([
                'seo',
                'specifications',
                'variants.images',
                'variants.storages'
            ]);

        });
    }

    public function updateSeo(int $productId, array $seoData)
    {
        $product = Product::findOrFail($productId);

        $seoData = Arr::only($seoData, [
            'seo_title',
            'seo_description',
            'seo_keywords',
            'seo_image'
        ]);


        if (!empty($seoData['seo_image']) && is_file($seoData['seo_image'])) {
            $seoData['seo_image'] = $this->imageService->replace($product->seo?->seo_image, $seoData['seo_image'], 'products');
        } elseif (isset($product->seo->seo_image)) {
            $seoData['seo_image'] = $product->seo->seo_image;
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
        $product->update(['status' => 0]);


        return $product->load('seo');
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








    protected function handleSpecifications(Product $product, array $specifications)
    {
        $incomingIds = collect($specifications)->pluck('id')->filter()->all();

        /** ------------------------------
         * Delete removed specifications
         * ----------------------------- */
        $product->specifications()
            ->whereNotIn('id', $incomingIds)
            ->delete();

        foreach ($specifications as $index => $specData) {

            /** ------------------------------
             * Find or create spec (ID-safe)
             * ----------------------------- */
            if (!empty($specData['id'])) {
                $spec = ProductSpecification::withTrashed()
                    ->where('id', $specData['id'])
                    ->where('product_id', $product->id)
                    ->firstOrFail();

                if ($spec->trashed()) {
                    $spec->restore();
                }
            } else {
                $spec = new ProductSpecification([
                    'product_id' => $product->id,
                ]);
            }

            /** ------------------------------
             * Update only when dirty
             * ----------------------------- */
            $spec->fill([
                'title' => $specData['title'],
                'sort_order' => $specData['sort_order'] ?? ($index + 1),
                'specification_items' => collect($specData['items'] ?? [])
                    ->map(fn($item, $i) => [
                        'key' => $item['key'],
                        'value' => $item['value'],
                        'order' => $item['order'] ?? ($i + 1),
                    ])->toArray(),
            ]);

            if ($spec->isDirty() || !$spec->exists) {
                $spec->save();
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
