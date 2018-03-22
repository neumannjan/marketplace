<?php

namespace App;


use App\Eloquent\AuthorizationAwareModel;
use App\Eloquent\Order\OrderAware;
use App\Eloquent\Order\OrderAwareModel;
use App\Observers\OfferObserver;
use App\Rules\CurrencyRule;
use App\Rules\MoneyRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Laravel\Scout\Searchable;
use Money\Money;

/**
 * App\Offer
 */
class Offer extends Model implements AuthorizationAwareModel, OrderAwareModel
{
    use Searchable, OrderAware;

    const STATUS_DRAFT = 0;
    const STATUS_AVAILABLE = 1;
    const STATUS_SOLD = 2;

    const SCOPE_PUBLIC = 'public';
    const SCOPE_AUTH = 'auth';
    const SCOPE_UNLIMITED = 'unlimited';

    protected $dates = [
        'created_at',
        'updated_at',
        'listed_at'
    ];

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_value',
        'currency',
        'currency_code',
        'status',
        'author_user_id',
        'sold_to_user_id',
    ];

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
     * @inheritDoc
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(OfferObserver::class);

        static::addGlobalScope('order', function (Builder $query) {
            return $query
                ->orderBy('listed_at', 'desc');
        });
    }

    /**
     * @inheritdoc
     */
    function getOrderBy()
    {
        return ['listed_at', 'created_at', 'id'];
    }

    /**
     * @return Money|null
     */
    public function getMoneyAttribute()
    {
        if (!$this->price_value || $this->price_value <= 0 || !is_string($this->currency_code)) {
            return null;
        }

        return \Money::getDecimalParser()->parse((string)$this->price_value, $this->currency_code);
    }

    /**
     * @return string
     */
    public function getPriceAttribute()
    {
        if (!$this->money) {
            return 'FREE';
        } //TODO

        return \Money::getFormatter()->format($this->money);
    }

    public function setPriceAttribute($price)
    {
        $this->price_value = $price;
    }

    public function setCurrencyAttribute($currency)
    {
        $this->currency_code = $currency;
    }

    /**
     * Returns the date that offers have to be newer than to not be considered expired
     * @return Carbon
     */
    protected function expiredFromTimestamp()
    {
        //TODO is 2 months too soon?
        return Carbon::now()->subMonths(2);
    }

    /**
     * Returns the date that offers have to be newer than to not be removed
     * @return Carbon
     */
    public function removedFromTimestamp()
    {
        return Carbon::now()->subYears(1);
    }

    /**
     * Whether this offer is expired
     * @return bool
     */
    public function getExpiredAttribute()
    {
        return $this->status === self::STATUS_AVAILABLE && $this->listed_at->lessThan($this->expiredFromTimestamp());
    }

    /**
     * Whether this offer can be displayed
     * @return bool
     */
    public function getDisplayableAttribute()
    {
        return !$this->expired
            && $this->status === self::STATUS_AVAILABLE
            && $this->author->status === User::STATUS_ACTIVE;
    }

    /**
     * Limits the query to only return items that should be removed
     * @param Builder $query
     * @return Builder
     */
    public function scopeToBeRemoved(Builder $query)
    {
        return $query->whereDate('listed_at', '<', $this->removedFromTimestamp());
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

        // return only offers that are not expired
        $query->whereDate('listed_at', '>=', $this->expiredFromTimestamp());

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
     * Limits the query to only return items that are accessible publicly and items that the current user owns.
     * @param Builder $query
     * @return Builder
     */
    public function scopeAuth(Builder $query)
    {
        return $query
            ->addNestedWhereQuery($this->scopePublic($this->newQuery())->getQuery())
            ->orWhere(['author_user_id' => \Auth::user()->id]);
    }

    /**
     * @inheritDoc
     */
    public function getPublicScopes()
    {
        return [self::SCOPE_PUBLIC, self::SCOPE_AUTH, self::SCOPE_UNLIMITED];
    }

    /**
     * @inheritDoc
     */
    public function canUsePublicScope($scopeName, User $user = null)
    {
        switch ($scopeName) {
            case self::SCOPE_PUBLIC:
                return true;
            case self::SCOPE_AUTH:
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
                        'author/username',
                        'author/id',
                        'author/email',
                        'price_value',
                        'currency_code'
                    ]))
                    ->isEmpty();
            case self::SCOPE_AUTH:
                return Collection::wrap($columnNames)
                    ->diff(Collection::make([
                        'id',
                        'name',
                        'listed_at',
                        'author_user_id',
                        'author/username',
                        'author/id',
                        'author/email',
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

    /**
     * @inheritDoc
     */
    public function toSearchableArray()
    {
        return Arr::only($this->toArray(), ['id', 'name', 'description']);
    }

    /**
     * Get validation rules for a creation request.
     * @param Validator $validator
     * @return array
     */
    public static function getValidationRules(Validator $validator = null)
    {
        if ($validator) {
            $validator->sometimes('currency', resolve(CurrencyRule::class), function ($input) {
                return $input->price && $input->price > 0;
            });

            $validator->sometimes('price', ['required', new MoneyRule()], function ($input) {
                return intval($input->status) === Offer::STATUS_AVAILABLE;
            });

            $validator->sometimes('images', 'required', function ($input) {
                $val = intval($input->status) === Offer::STATUS_AVAILABLE;
                return $val;
            });

            $validator->sometimes('images', 'file|image', function ($input) {
                $val = intval($input->status) === Offer::STATUS_AVAILABLE && !is_array($input->images);
                return $val;
            });

            $validator->sometimes('images.*', 'file|image', function ($input) {
                $val = intval($input->status) === Offer::STATUS_AVAILABLE && is_array($input->images);
                return $val;
            });
        }

        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|min:5|max:2000',
            'currency' => '',
            'price' => '',
            'images' => '',
            'status' => Rule::in([Offer::STATUS_DRAFT, Offer::STATUS_AVAILABLE]),
        ];
    }
}