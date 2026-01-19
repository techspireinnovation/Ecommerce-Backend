<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_storage_id',
        'quantity',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariantStorage()
    {
        return $this->belongsTo(ProductVariantStorage::class);
    }

}
