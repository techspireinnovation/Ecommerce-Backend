<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Models\SeoDetail;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Services\ImageService;
use App\Services\SlugService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandRepository implements BrandRepositoryInterface
{
    protected ImageService $imageService;
    protected SlugService $slugService;

    public function __construct(ImageService $imageService, SlugService $slugService)
    {
        $this->imageService = $imageService;
        $this->slugService = $slugService;
    }

    public function all(): LengthAwarePaginator
    {
        return Brand::with('seo')
        ->whereNull('deleted_at')
        ->orderBy('id', 'desc')
        ->paginate(20);
    }

    public function find(int $id): Brand
    {
        return Brand::with('seo')->whereNull('deleted_at')->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->store($data['image'], 'brands');
            }

            $data['slug'] = $this->slugService->createUniqueSlug($data['name'], Brand::class);

            $brand = Brand::create(Arr::only($data, ['name', 'slug', 'image', 'status']));
            $seoData = Arr::only($data, ['seo_title', 'seo_description', 'seo_keywords', 'seo_image']);
            if (!empty($seoData['seo_image'])) {
                $seoData['seo_image'] = $this->imageService->store($seoData['seo_image'], 'brands');
            }

            $seoData['reference_id'] = $brand->id;
            $seoData['type'] = 1; // 1=brand

            SeoDetail::create($seoData);

            return $brand->load('seo');
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $brand = Brand::findOrFail($id);

            if (!empty($data['image']) && is_file($data['image'])) {
                $data['image'] = $this->imageService->replace($brand->image, $data['image'], 'brands');
            } else {
                $data['image'] = $brand->image;
            }
            // Slug
            if (isset($data['name']) && $data['name'] !== $brand->name) {
                $data['slug'] = $this->slugService->createUniqueSlug($data['name'], Brand::class);
            } else {
                $data['slug'] = $brand->slug; // keep existing slug if name unchanged
            }

            // Update brand
            $brand->update(Arr::only($data, ['name', 'slug', 'image', 'status']));

            // Update SEO
            $seoData = Arr::only($data, ['seo_title', 'seo_description', 'seo_keywords', 'seo_image']);
            if (!empty($seoData['seo_image']) && is_file($seoData['seo_image'])) {
                $seoData['seo_image'] = $this->imageService->replace($brand->seo?->seo_image, $seoData['seo_image'], 'brands');
            } elseif (isset($brand->seo->seo_image)) {
                $seoData['seo_image'] = $brand->seo->seo_image;
            }

            if ($brand->seo) {
                $brand->seo->update($seoData);
            } else {
                $seoData['reference_id'] = $brand->id;
                $seoData['type'] = 1;
                SeoDetail::create($seoData);
            }

            return $brand->load('seo');
        });
    }

    public function delete(int $id)
    {
        $brand = Brand::findOrFail($id);
        $this->imageService->delete($brand->image);
        $this->imageService->delete($brand->seo?->seo_image);
        $brand->delete($id);
    }
    public function activeList()
    {
        return Brand::query()
            ->where('status', 0)
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

}
