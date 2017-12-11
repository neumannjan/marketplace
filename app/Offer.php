<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Offer
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $name
 * @property string $description
 * @property int $author_user_id
 * @property int $status 0 == inactive, 1 == available, 2 == sold
 * @property int|null $sold_to_user_id
 * @property-read \App\User $author
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Image[] $images
 * @property-read \App\User $soldTo
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereAuthorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereSoldToUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Offer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Offer extends Model
{
    const STATUS_INACTIVE = 0;
    const STATUS_AVAILABLE = 1;
    const STATUS_SOLD = 2;

    public function images()
    {
        return $this->hasMany(Image::class, 'offer_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, null, 'author_user_id');
    }

    public function soldTo()
    {
        return $this->belongsTo(User::class, null, 'sold_to_user_id');
    }
}