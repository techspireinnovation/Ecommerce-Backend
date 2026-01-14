<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
    ];
    public function seo()
    {
        return $this->hasOne(SeoDetail::class, 'reference_id')
            ->where('type', 1);
    }
    protected static function booted()
    {
        static::deleting(function (Brand $brand) {
            if ($brand->seo) {
                $brand->seo->delete();
            }
        });
    }
}
