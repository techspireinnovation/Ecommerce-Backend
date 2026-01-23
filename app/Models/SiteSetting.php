<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = 'site_settings';

    protected $fillable = [
        'store_name',
        'primary_mobile_no',
        'secondary_mobile_no',
        'primary_email',
        'secondary_email',
        'address_id',
        'logo_image',
        'fav_icon_image',
        'instagram_link',
        'facebook_link',
        'whatsapp_link',
        'linkedin_link',
    ];

    /**
     * Relationship: SiteSetting belongs to an Address
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }
}
