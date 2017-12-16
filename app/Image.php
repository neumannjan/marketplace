<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Image
 */
class Image extends Model
{
    const SIZES = ['original', 'tiny', 'icon'];

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'profile_image_id');
    }
}