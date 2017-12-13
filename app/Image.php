<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Image
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $size_original
 * @property string|null $size_tiny
 * @property int|null $offer_id
 * @property-read \App\Offer|null $offer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Image whereOfferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Image whereSizeOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Image whereSizeTiny($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Image whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Image extends Model
{
    const SIZES = ['original', 'tiny'];

    public function offer()
    {
        return $this->belongsTo(Offer::class, null, 'offer_id');
    }
}