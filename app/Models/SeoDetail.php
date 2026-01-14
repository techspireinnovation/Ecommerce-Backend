<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'seo_details';

    protected $fillable = [
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_image',
        'reference_id',
        'type',
    ];

    protected $casts = [
        'seo_keywords' => 'array',
    ];

}
