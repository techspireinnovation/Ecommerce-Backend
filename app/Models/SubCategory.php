<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "sub_categories";
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'image',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];

    public function seo()
    {
        return $this->hasOne(SeoDetail::class, 'reference_id')
            ->where('type', 3);
    }
    protected static function booted()
    {
        static::deleting(function (SubCategory $subcategory) {
            if ($subcategory->seo) {
                $subcategory->seo->delete($subcategory);
            }
        });
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}