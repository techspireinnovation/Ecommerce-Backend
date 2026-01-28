<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use SoftDeletes;

    protected $table = 'shipping_methods';

    protected $fillable = [
        'delivery_type',
        'weight_to',
        'charge',
        'free_shipping_threshold',
    ];

    protected $casts = [
        'delivery_type' => 'integer',
        'weight_to' => 'decimal:2',
        'charge' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
    ];

}
