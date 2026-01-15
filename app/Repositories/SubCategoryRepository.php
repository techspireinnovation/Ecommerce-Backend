<?php
namespace App\Repositories;

use App\Models\SeoDetail;
use App\Models\SubCategory;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Services\ImageService;
use App\Services\SlugService;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
class SubCategoryRepository implements SubCategoryRepositoryInterface
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
        return SubCategory::with('seo')->get();
    }
    public function find(int $id)
    {
        return SubCategory::with('seo')->findOrFail($id);
    }
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->store($data['image'], 'subcategories');
            }
            $data['slug'] = $this->slugService->createUniqueSlug($data['name'], SubCategory::class);

            $subCategory = SubCategory::create(Arr::only($data, ['name', 'category_id', 'slug', 'image', 'status']));

            $seoData = Arr::only($data, ['seo_title', 'seo_description', 'seo_keywords', 'seo_image']);
            if (!empty($seoData['seo_image'])) {
                $seoData['seo_image'] = $this->imageService->store($seoData['seo_image'], 'subcategories');
            }
            $seoData['reference_id'] = $subCategory->id;
            $seoData['type'] = 3;

            SeoDetail::create($seoData);
            return $subCategory->load('seo');

        });
    }
    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $subCategory = SubCategory::findOrFail($id);
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->replace($subCategory->image, $data['image'], 'subcategories');
            }
            if (isset($data['name']) && $data['name'] != $subCategory->name) {
                $data['slug'] = $this->slugService->createUniqueSlug($data['name'], SubCategory::class);
            } else {
                $data['slug'] = $subCategory->slug;
            }
            $subCategory->update(Arr::only($data, ['name', 'category_id', 'slug', 'image', 'status']));
            $seoData = Arr::only($data, ['seo_title', 'seo_description', 'seo_keywords', 'seo_image']);
            if (!empty($seoData['seo_image'])) {
                $seoData['seo_image'] = $this->imageService->replace($subCategory->seo?->seo_image, $seoData['seo_image'], 'subcategories');
            }
            if ($subCategory->seo) {
                $subCategory->seo->update($seoData);
            } else {
                $seoData['reference_id'] = $subCategory->id;
                $seoData['type'] = 3;
                SeoDetail::Create($seoData);

            }
            return $subCategory->load('seo');
        });
    }
    public function delete(int $id)
    {
        $subcategory = SubCategory::findOrFail($id);
        $this->imageService->delete($subcategory->image);
        $this->imageService->delete($subcategory->seo?->seo_image);
        $subcategory->delete($id);

    }
    public function activeList(){
        return SubCategory::query()
            ->where('status', 0)
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

}