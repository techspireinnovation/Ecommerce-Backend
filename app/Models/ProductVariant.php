<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'color',
        'images',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function storages()
    {
        return $this->hasMany(ProductVariantStorage::class);
    }
    protected static function booted()
    {
        static::deleting(function ($variant) {
            if ($variant->isForceDeleting()) return;
            $variant->storages()->each(fn($storage) => $storage->delete());
        });

    }
}
