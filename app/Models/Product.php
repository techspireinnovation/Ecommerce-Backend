<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'slug',
        'name',
        'brand_id',
        'subcategory_id',
        'summary',
        'overview',
        'price',
        'discount_percentage',
        'highlights',
        'policies',
        'weight',
        'tags',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'highlights' => 'array',
        'policies' => 'array',
        'tags' => 'array',
    ];

    public function seo()
    {
        return $this->hasOne(SeoDetail::class, 'reference_id')
            ->where('type', 4);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }


    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }


    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    protected static function booted()
    {
        static::deleting(function ($product) {
            if ($product->isForceDeleting()) {
                return;
            }

            $product->specifications()->each(fn($spec) => $spec->delete());
            $product->variants()->each(fn($variant) => $variant->delete());
            $product->seo()->each(fn($seo) => $seo->delete());
        });

    }
}
