<?php

namespace App\Services;

use App\Models\ProductVariantStorage;
use App\Models\Brand;
use Illuminate\Support\Str;

class SkuService
{
    /**
     * Generate SKU based on product, brand, color, storage
     */
    public function generateByBrandId(int $brandId, string $productName, string $color, string $storage): string
    {
        $brand = Brand::query()->find($brandId);
        $brandName = $brand?->name ?? 'BRND'; // fallback if brand not found

        return $this->generate($brandName, $productName, $color, $storage);
    }


    /**
     * Generate SKU based on brand name, product name, color, storage
     */
    public function generate(string $brand, string $productName, string $color, string $storage): string
    {
        $brandCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $brand), 0, 4));
        $productCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $productName), 0, 4));
        $colorCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $color), 0, 3));
        $storageCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $storage));

        $sku = "{$brandCode}-{$productCode}-{$colorCode}-{$storageCode}";

        // ensure unique
        if ($this->exists($sku)) {
            $sku .= '-' . strtoupper(substr(uniqid(), -4));
        }

        return $sku;
    }

    /**
     * Check if SKU exists in database
     */
    protected function exists(string $sku): bool
    {
        return ProductVariantStorage::query()
            ->where('sku', $sku)
            ->whereNull('deleted_at')
            ->exists();
    }


    /**
     * Generate SKUs for multiple storages
     * Accepts brand_id instead of brand name
     */
    public function generateForStorages(array $storages, int $brandId, string $productName, string $color): array
    {
        return collect($storages)->map(function ($storage) use ($brandId, $productName, $color) {
            // Only regenerate if sku is missing or needs to change
            if (empty($storage['sku']) || $storage['regenerate'] ?? false) {
                $storage['sku'] = $this->generateByBrandId($brandId, $productName, $color, $storage['storage']);
            }
            return $storage;
        })->toArray();
    }
}
