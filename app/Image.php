<?php

namespace App;


use App\Observers\ImageObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Image
 */
class Image extends Model
{
    const STORAGE_DIR = 'public' . DIRECTORY_SEPARATOR . 'images';

    const SIZES = [
        'tiny' => 0.05,
        'icon' => [40, 40],
        'icon_2x' => [80, 80]
    ];
    const ORIGINAL_SIZE = 'original';

    protected $casts = [
        'sizes' => 'array',
        'available_sizes' => 'array',
        'ready' => 'boolean'
    ];

    protected $fillable = [
        'sizes',
        'original',
        'ready',
        'width',
        'height',
        'offer_id',
        'available_sizes'
    ];

    /**
     * @inheritDoc
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(ImageObserver::class);
    }

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
        $sizes = $this->sizes ?: [];

        return [
                self::ORIGINAL_SIZE => $this->attributes['original']
            ] + $sizes;
    }

    /**
     * Array of image URLs
     * @return string[]
     */
    public function getUrlsAttribute()
    {
        $urls = $this->all_sizes;

        if (!$urls) {
            return $urls;
        }

        foreach ($urls as $key => $url) {
            if (!Str::startsWith($url, ['http://', 'https://'])) {
                $urls[$key] = \Storage::url($url);
            }
        }

        return $urls;
    }

    /**
     * Array of image absolute paths
     * @return string[]
     */
    public function getAbsolutePathsAttribute()
    {
        $paths = $this->all_sizes;

        if (!$paths) {
            return $paths;
        }

        foreach ($paths as $key => $path) {
            if (Str::startsWith($path, ['http://', 'https://'])) {
                unset($paths[$key]);
            } else {
                $paths[$key] = \App::storagePath() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $path;
            }
        }

        return $paths;
    }
}