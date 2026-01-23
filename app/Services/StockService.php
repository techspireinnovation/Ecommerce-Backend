<?php

namespace App\Services;

use App\Models\ProductVariantStorage;

class StockService
{
    /**
     * Get available stock quantity for a variant
     */
    public function getAvailableQuantity(int $variantStorageId): int
    {
        return ProductVariantStorage::query()
            ->where('id', $variantStorageId)
            ->whereNull('deleted_at')
            ->value('quantity') ?? 0;
    }

    /**
     * FINAL validation used by CartRequest
     */
    public function isQuantityAvailableForCart(int $variantStorageId, int $requestedQuantity): bool
    {
        $stock = $this->getAvailableQuantity($variantStorageId);

        return $requestedQuantity <= $stock;
    }
}
