<?php
namespace App\Repositories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\SeoDetail;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\ImageService;
use App\Services\SlugService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
class CategoryRepository implements CategoryRepositoryInterface
{
    private ImageService $imageService;
    private SlugService $slugService;

    public function __construct(ImageService $imageService, SlugService $slugService)
    {
        $this->imageService = $imageService;
        $this->slugService = $slugService;
    }

    public function all()
    {
        return Category::with('seo')->get();
    }
    public function find(int $id)
    {
        return Category::with('seo')->findOrFail($id);
    }
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->store($data['image'], 'categories');
            }
            $data['slug'] = $this->slugService->createUniqueSlug($data['name'], Category::class);
            $category = Category::create(Arr::only($data, ['name', 'slug', 'image', 'status']));

            $seoData = Arr::only($data, ['seo_title', 'seo_description', 'seo_keywords', 'seo_image']);
            if (!empty($seoData['seo_image'])) {
                $seoData['seo_image'] = $this->imageService->store($seoData['seo_image'], 'categories');
            }
            $seoData['reference_id'] = $category->id;
            $seoData['type'] = 2;
            SeoDetail::create($seoData);
            return $category->load('seo');

        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $category = Category::findOrFail($id);
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->replace($category->image, $data['image'], 'categories');
            }
            if (isset($data['name']) && $data['name'] != $category->name) {
                $data['slug'] = $this->slugService->createUniqueSlug($data['name'], Category::class);
            } else {
                $data['slug'] = $category->slug;
            }

            $category->update(Arr::only($data, ['name', 'slug', 'image', 'status']));
            $seoData = Arr::only($data, ['seo_title', 'seo_description', 'seo_keywords', 'seo_image']);
            if (!empty($seoData['seo_image'])) {
                $seoData['seo_image'] = $this->imageService->replace($category->seo?->seo_image, $seoData['seo_image'], 'categories');
            }
            if ($category->seo) {
                $category->seo->update($seoData);

            } else {
                $seoData['reference_id'] = $category->id;
                $seoData['type'] = 2;
                SeoDetail::create($seoData);
            }

            return $category->load('seo');

        });

    }

    public function delete(int $id)
    {
        $category = Category::findOrFail($id);
        $activeCount = $category->subcategories
            ->where('status', 0)
            ->whereNull('deleted_at')
            ->count();
        if ($activeCount > 0) {
            throw new ValidationException(
                Validator::make([], []),
                response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this category because it has active subcategories.',
                ], 422)
            );

        }
        $this->imageService->delete($category->image);
        $this->imageService->delete($category->seo?->seo_image);
        $category->delete($id);

    }

    public function activeList()
    {
        return Category::query()
            ->where('status', 0)
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

}