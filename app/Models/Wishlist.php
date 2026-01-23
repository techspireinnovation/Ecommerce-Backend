<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use SoftDeletes, BelongsToUser;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_variant_storage_id',
        'status',
    ];

    /* ================= Relations ================= */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variantStorage()
    {
        return $this->belongsTo(ProductVariantStorage::class, 'product_variant_storage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ================= Scopes ================= */

    public function scopeForAuthUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
