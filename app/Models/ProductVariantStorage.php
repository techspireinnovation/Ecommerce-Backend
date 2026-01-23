<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariantStorage extends Model
{
    use SoftDeletes;

    protected $table = 'product_variant_storages';

    protected $fillable = [
        'product_variant_id',
        'storage',
        'sku',
        'quantity',
        'low_stock_threshold',
    ];

    /* =======================
     | Relationships
     ======================= */

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
