<?php

namespace App;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Image
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $size_original
 * @property string|null $size_tiny
 * @property int|null $offer
 * @method static Builder|Image whereCreatedAt($value)
 * @method static Builder|Image whereId($value)
 * @method static Builder|Image whereOffer($value)
 * @method static Builder|Image whereSizeOriginal($value)
 * @method static Builder|Image whereSizeTiny($value)
 * @method static Builder|Image whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Image extends Model
{
    public function offer()
    {
        return $this->belongsTo(Offer::class, null, 'offer');
    }
}