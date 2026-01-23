<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'deals';

    protected $fillable = [
        'title',
        'description',
        'image',
        'type',
        'amount',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'type' => 'integer',
        'status' => 'integer',
        'amount' => 'integer',
    ];

    // Relationship with DealProduct
    public function products()
    {
        return $this->belongsToMany(Product::class, 'deal_products', 'deal_id', 'product_id')
            ->wherePivotNull('deleted_at')
            ->withTimestamps()
            ->using(DealProduct::class);
    }


}
