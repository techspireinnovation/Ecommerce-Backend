<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealProduct extends Pivot
{
    use HasFactory, SoftDeletes;

    protected $table = 'deal_products';

    protected $fillable = [
        'deal_id',
        'product_id',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
