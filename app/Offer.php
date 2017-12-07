<?php
/**
 * Created by PhpStorm.
 * User: nojmy
 * Date: 7.12.17
 * Time: 13:42
 */

namespace App;


use App\Image;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Offer
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property string $description
 * @property int $author
 * @property int $status 0 == inactive, 1 == available, 2 == sold
 * @property int|null $sold_to
 * @method static Builder|Offer whereAuthor($value)
 * @method static Builder|Offer whereCreatedAt($value)
 * @method static Builder|Offer whereDescription($value)
 * @method static Builder|Offer whereId($value)
 * @method static Builder|Offer whereName($value)
 * @method static Builder|Offer whereSoldTo($value)
 * @method static Builder|Offer whereStatus($value)
 * @method static Builder|Offer whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read Collection|Image[] $images
 * @property-read \App\User $soldTo
 */
class Offer extends Model
{

    public function images()
    {
        return $this->hasMany(Image::class, 'offer');
    }

    public function author()
    {
        return $this->belongsTo(User::class, null, 'author');
    }

    public function soldTo()
    {
        return $this->belongsTo(User::class, null, 'sold_to');
    }
}