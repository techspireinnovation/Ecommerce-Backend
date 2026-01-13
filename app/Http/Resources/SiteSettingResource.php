<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
{
    public function toArray($request): array
    {
        if (!$this->resource) { 
            return [];
        }

        return [
            'id' => $this->id,
            'store_name' => $this->store_name,
            'primary_mobile' => $this->primary_mobile_no,
            'secondary_mobile' => $this->secondary_mobile_no,
            'primary_email' => $this->primary_email,
            'secondary_email' => $this->secondary_email,
            'logo_url' => $this->logo_image ? asset('storage/' . $this->logo_image) : null,
            'favicon_url' => $this->fav_icon_image ? asset('storage/' . $this->fav_icon_image) : null,
            'instagram' => $this->instagram_link,
            'facebook' => $this->facebook_link,
            'whatsapp' => $this->whatsapp_link,
            'linkedin' => $this->linkedin_link,
            'label' => $this->address?->label,
            'street' => $this->address?->street,
            'city' => $this->address?->city,
            'district' => $this->address?->district,
            'province' => $this->address?->province,
            'zip' => $this->address?->zip,
            'latitude' => $this->address?->latitude,
            'longitude' => $this->address?->longitude,
        ];
    }

}
