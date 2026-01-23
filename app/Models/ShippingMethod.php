<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use SoftDeletes;

    protected $table = 'shipping_methods';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'delivery_type',
        'charge_amount',
        'free_delivery_min_amount',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'delivery_type' => 'integer',
        'charge_amount' => 'decimal:2',
        'free_delivery_min_amount' => 'decimal:2',
        'status' => 'integer',
    ];

   
}
