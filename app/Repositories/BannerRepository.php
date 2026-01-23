<?php

namespace App\Repositories;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BannerRepository implements BannerRepositoryInterface
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function all()
    {
        return Banner::query()->latest()->whereNull('deleted_at')->paginate(20);
    }

    public function find(int $id)
    {
        return Banner::query()->whereNull('deleted_at')->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            if (!empty($data['image'])) {
                $data['image'] = $this->imageService->store($data['image'], 'banners');
            }

            return Banner::create(Arr::only($data, [
                'title',
                'type',
                'image',
                'status',
            ]));
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $banner = Banner::findOrFail($id);

            if (!empty($data['image']) && is_file($data['image'])) {
                $data['image'] = $this->imageService->replace($banner->image, $data['image'], 'banners');
            } else {
                $data['image'] = $banner->image;
            }

            $banner->update(Arr::only($data, [
                'title',
                'type',
                'image',
                'status',
            ]));

            return $banner;
        });
    }

    public function delete(int $id)
    {
        $banner = Banner::findOrFail($id);
        $this->imageService->delete($banner->image);
        $banner->delete($id);
    }


}
