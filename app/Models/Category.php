<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "categories";

    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];
    public function seo()
    {
        return $this->hasOne(SeoDetail::class, 'reference_id')
            ->where('type', 2);
    }
    public function subcategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }
    protected static function booted()
    {
        static::deleting(function (Category $category) {
            if ($category->seo) {
                $category->seo->delete($category);
            }
        });
    }
}
