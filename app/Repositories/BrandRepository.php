<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Models\SeoDetail;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandRepository implements BrandRepositoryInterface
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function all()
    {
        return Brand::with('seo')->get();
    }

    public function find(int $id)
    {
        return Brand::with('seo')->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Brand image
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->store($data['image'], 'brands');
            }

            // Slug
            $data['slug'] = Str::slug($data['name']);

            // Create brand
            $brand = Brand::create(Arr::only($data, ['name', 'slug', 'image', 'status']));

            // SEO
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

            // Brand image
            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->replace($brand->image, $data['image'], 'brands');
            }

            // Slug
            $data['slug'] = Str::slug($data['name']);

            // Update brand
            $brand->update(Arr::only($data, ['name', 'slug', 'image', 'status']));

            // Update SEO
            $seoData = Arr::only($data, ['seo_title', 'seo_description', 'seo_keywords', 'seo_image']);
            if (!empty($seoData['seo_image'])) {
                $seoData['seo_image'] = $this->imageService->replace($brand->seo?->seo_image, $seoData['seo_image'], 'brands');
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
        $brand->delete();
    }
}
