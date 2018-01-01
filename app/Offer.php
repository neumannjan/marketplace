<?php

namespace App;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Money\Money;

/**
 * App\Offer
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
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function soldTo()
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
    }

    /**
     * @return Money
     */
    public function getMoneyAttribute()
    {
        return \Money::getDecimalParser()->parse((string)$this->price_value, $this->currency_code);
    }

    /**
     * @return string
     */
    public function getPriceAttribute()
    {
        return \Money::getFormatter()->format($this->money);
    }

    public function scopeActive(Builder $query)
    {
        return $query
            ->where(['status' => self::STATUS_AVAILABLE])
            ->whereHas('author', function (Builder $query) {
                $query->where(['status' => User::STATUS_ACTIVE]);
            });
    }
}