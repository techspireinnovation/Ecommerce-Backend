<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSpecification extends Model
{
    use SoftDeletes;

    protected $table = 'product_specifications';

    protected $fillable = [
        'product_id',
        'title',
        'specification_items',
        'sort_order',
    ];

    protected $casts = [
        'specification_items' => 'array',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
   

}
