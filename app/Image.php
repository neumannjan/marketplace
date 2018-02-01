<?php

namespace App;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Image
 */
class Image extends Model
{
    const SUBDIR = 'images';

    const SIZES = [
        'tiny' => 0.05,
        'icon' => [40, 40],
        'icon_2x' => [80, 80]
    ];
    const ORIGINAL_SIZE = 'original';

    protected $casts = [
        'sizes' => 'array'
    ];

    protected $fillable = [
        'sizes', 'original', 'ready', 'width', 'height', 'offer_id'
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'profile_image_id');
    }

    /**
     * Return only images that can be displayed
     * @param Builder $query
     * @return Builder
     */
    public function scopeDisplayable(Builder $query)
    {
        return $query->where('ready', true);
    }

    /**
     * Array combining `sizes` and `original`
     * @return string[]
     */
    public function getAllSizesAttribute()
    {
        return [
                self::ORIGINAL_SIZE => $this->original
            ] + $this->sizes;
    }
}