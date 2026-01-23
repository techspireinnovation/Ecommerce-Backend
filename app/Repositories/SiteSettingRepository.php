<?php

namespace App\Repositories;

use App\Models\SiteSetting;
use App\Models\Address;
use App\Repositories\Interfaces\SiteSettingRepositoryInterface;
use App\Services\ImageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SiteSettingRepository implements SiteSettingRepositoryInterface
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get the single site setting record
     */
    public function get()
    {
        return SiteSetting::with('address')->first();
    }

    /**
     * Create or update the single site setting record along with site address
     */
    public function storeOrUpdate(array $data)
    {
        return DB::transaction(function () use ($data) {

            $siteSetting = SiteSetting::query()->first();
            if (!empty($data['logo_image']) && $data['logo_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['logo_image'] = $siteSetting
                    ? $this->imageService->replace(
                        $siteSetting->logo_image,
                        $data['logo_image'],
                        'site-settings'
                    )
                    : $this->imageService->store($data['logo_image'], 'site-settings');
            }

            if (!empty($data['fav_icon_image']) && $data['fav_icon_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['fav_icon_image'] = $siteSetting
                    ? $this->imageService->replace(
                        $siteSetting->fav_icon_image,
                        $data['fav_icon_image'],
                        'site-settings'
                    )
                    : $this->imageService->store($data['fav_icon_image'], 'site-settings');
            }

            $addressData = Arr::only($data, [
                'street',
                'city',
                'district',
                'province',
                'zip',
                'latitude',
                'longitude',
            ]);

            $addressData += [
                'type' => 2,
                'label' => 'Store Location',
                'status' => 0,
            ];

            $siteAddress = Address::updateOrCreate(
                ['type' => 2],
                $addressData
            );

            /**
             * Remove address fields from site setting payload
             */
            Arr::forget($data, [
                'street',
                'city',
                'district',
                'province',
                'zip',
                'latitude',
                'longitude',
            ]);

            $data['address_id'] = $siteAddress->id;


            $siteSetting = $siteSetting
                ? tap($siteSetting)->update($data)
                : SiteSetting::create($data);

            return $siteSetting->load('address');
        });
    }

}
