<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class SlugService
{
    /**
     * Generate a unique slug for a model, considering soft-deleted records.
     *
     * @param string $name
     * @param string $modelClass Full model class, e.g., \App\Models\Brand::class
     * @param string $slugColumn Column name to store slug, default 'slug'
     * @return string
     */
    public function createUniqueSlug(string $name, string $modelClass, string $slugColumn = 'slug'): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while ($modelClass::withTrashed()->where($slugColumn, $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
