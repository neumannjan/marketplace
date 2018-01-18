<?php

namespace App;


use App\Eloquent\AuthorizationAwareModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Money\Money;

/**
 * App\Offer
 */
class Offer extends Model implements AuthorizationAwareModel
{
    const STATUS_INACTIVE = 0;
    const STATUS_AVAILABLE = 1;
    const STATUS_SOLD = 2;

    const SCOPE_PUBLIC = 'public';
    const SCOPE_OWNED = 'owned';
    const SCOPE_UNLIMITED = 'unlimited';

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

    /**
     * Limits the query to only return items that are accessible publicly
     * @param Builder $query
     * @return Builder
     */
    public function scopePublic(Builder $query)
    {
        // ensure availability
        $query->where(['status' => self::STATUS_AVAILABLE]);

        // ensure that the author is an active user
        $query->whereHas('author', function (Builder $query) {
            $query->where(['status' => User::STATUS_ACTIVE]);
        });

        // return only offers newer than 2 months
        // TODO is it a good idea ?
        $query->whereDate('listed_at', '>=', Carbon::now()->subMonths(2));

        return $query;
    }

    /**
     * Does not limit the query
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnlimited(Builder $query)
    {
        return $query;
    }

    /**
     * Limits the query to only return items that the current user owns
     * @param Builder $query
     * @return Builder
     */
    public function scopeOwned(Builder $query)
    {
        return $query->where(['author_user_id' => \Auth::user()->id]);
    }

    /**
     * @inheritDoc
     */
    public function getPublicScopes()
    {
        return [self::SCOPE_PUBLIC, self::SCOPE_OWNED, self::SCOPE_UNLIMITED];
    }

    /**
     * @inheritDoc
     */
    public function canUsePublicScope($scopeName, User $user = null)
    {
        switch ($scopeName) {
            case self::SCOPE_PUBLIC:
                return true;
            case self::SCOPE_OWNED:
                return $user && \Auth::check() && $user->id === \Auth::id();
            case self::SCOPE_UNLIMITED:
                return $user && $user->is_admin ? true : false;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function validatePublicScopeParams($scopeName, $columnNames)
    {
        switch ($scopeName) {
            case self::SCOPE_PUBLIC:
                return Collection::wrap($columnNames)
                    ->diff(Collection::make([
                        'id',
                        'name',
                        'listed_at',
                        'author_user_id',
                        'price_value',
                        'currency_code'
                    ]))
                    ->isEmpty();
            case self::SCOPE_OWNED:
                return Collection::wrap($columnNames)
                    ->diff(Collection::make([
                        'id',
                        'name',
                        'listed_at',
                        'author_user_id',
                        'price_value',
                        'currency_code',
                        'status'
                    ]))
                    ->isEmpty();
            case self::SCOPE_UNLIMITED:
                return true;
        }

        return false;
    }
}