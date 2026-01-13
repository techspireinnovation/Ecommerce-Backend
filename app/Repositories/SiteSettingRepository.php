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

            // Get existing site setting (only one record)
            $siteSetting = SiteSetting::first();

            // Handle logo image
            if (isset($data['logo_image']) && $data['logo_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($siteSetting?->logo_image) {
                    $this->imageService->delete($siteSetting->logo_image);
                }
                $data['logo_image'] = $this->imageService->store($data['logo_image'], 'site-settings');
            }

            // Handle favicon image
            if (isset($data['fav_icon_image']) && $data['fav_icon_image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($siteSetting?->fav_icon_image) {
                    $this->imageService->delete($siteSetting->fav_icon_image);
                }
                $data['fav_icon_image'] = $this->imageService->store($data['fav_icon_image'], 'site-settings');
            }

            // Extract address fields
            $addressData = Arr::only($data, ['street', 'city', 'district', 'province', 'zip', 'latitude', 'longitude']);
            $addressData['type'] = 2; // Site
            $addressData['label'] = 'Store Location';
            $addressData['status'] = 0;

            // Create or update the address (only one site address allowed)
            $siteAddress = Address::where('type', 2)->first();
            if ($siteAddress) {
                $siteAddress->update($addressData);
            } else {
                $siteAddress = Address::create($addressData);
            }

            // Remove address fields from site setting data
            foreach (['street', 'city', 'district', 'province', 'zip', 'latitude', 'longitude'] as $field) {
                unset($data[$field]);
            }

            // Add address_id to site setting
            $data['address_id'] = $siteAddress->id;

            // Create or update site setting
            if ($siteSetting) {
                $siteSetting->update($data);
            } else {
                $siteSetting = SiteSetting::create($data);
            }

            // Return the updated site setting with address relation
            return $siteSetting->load('address');
        });
    }
}
