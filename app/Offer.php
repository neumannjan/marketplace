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
use Money\Currency;
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
    const SCOPE_REPORTED = 'reported';

    const MAX_BUMP_TIMES = 2;

    protected $dates
        = [
            'created_at',
            'updated_at',
            'listed_at',
        ];

    protected $with
        = [
            'images',
            'author',
        ];

    protected $fillable
        = [
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

    public function reportedBy()
    {
        return $this->belongsToMany(User::class, 'user_offer_reports',
            'offer_id', 'user_id', 'id', 'id');
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
        if ( ! $this->price_value || $this->price_value <= 0
            || ! is_string($this->currency_code)
        ) {
            return null;
        }

        return \Money::getDecimalParser()->parse((string)$this->price_value,
            new Currency($this->currency_code));
    }

    /**
     * @return string|null
     */
    public function getPriceAttribute()
    {
        if ( ! $this->money) {
            return null;
        }

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
     *
     * @return Carbon
     */
    protected function expiredFromTimestamp()
    {
        //TODO is 2 months too soon?
        return Carbon::now()->subMonths(2);
    }

    /**
     * Returns the date that offers have to be newer than to not be removed
     *
     * @return Carbon
     */
    public function removedFromTimestamp()
    {
        return Carbon::now()->subYears(1);
    }

    /**
     * Whether this offer is expired
     *
     * @return bool
     */
    public function getExpiredAttribute()
    {
        return $this->status === self::STATUS_AVAILABLE
            && $this->listed_at->lessThan($this->expiredFromTimestamp());
    }

    /**
     * Whether this offer can be displayed
     *
     * @return bool
     */
    public function getDisplayableAttribute()
    {
        return ! $this->expired
            && $this->status === self::STATUS_AVAILABLE
            && $this->author->status === User::STATUS_ACTIVE;
    }

    /**
     * The amount of times this offer can be bumped
     *
     * @return int
     */
    public function getBumpsLeftAttribute()
    {
        return max(0, self::MAX_BUMP_TIMES - $this->bumped_times);
    }

    /**
     * Whether this offer has just been created / bumped
     *
     * @return bool
     */
    public function getJustBumpedAttribute()
    {
        return $this->listed_at->addHours(1)->greaterThan(Carbon::now());
    }

    /**
     * Limits the query to only return items that should be removed
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeToBeRemoved(Builder $query)
    {
        return $query->whereDate('listed_at', '<',
            $this->removedFromTimestamp());
    }

    /**
     * Limits the query to only return items that are accessible publicly
     *
     * @param Builder $query
     *
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
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeUnlimited(Builder $query)
    {
        return $query;
    }

    /**
     * Limits the query to only return items that are accessible publicly and that the current user owns.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeAuth(Builder $query)
    {
        return $query
            ->addNestedWhereQuery($this->scopePublic($this->newQuery())
                ->getQuery())
            ->orWhere(['author_user_id' => \Auth::user()->id]);
    }

    /**
     * Limits the query to only return items that have been reported to administrators.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeReported(Builder $query)
    {
        return $query
            ->where('reported_times', '>', 0)
            ->orderByDesc('reported_times');
    }

    /**
     * @inheritDoc
     */
    public function getPublicScopes()
    {
        return [
            self::SCOPE_PUBLIC,
            self::SCOPE_AUTH,
            self::SCOPE_UNLIMITED,
            self::SCOPE_REPORTED,
        ];
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
            case self::SCOPE_REPORTED:
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
                        'currency_code',
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
                        'status',
                    ]))
                    ->isEmpty();
            case self::SCOPE_UNLIMITED:
            case self::SCOPE_REPORTED:
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
     *
     * @param Validator $validator
     *
     * @return array
     */
    public static function getValidationRules(
        Validator $validator = null,
        $requireImages = true
    ) {
        if ($validator) {
            $validator->sometimes('currency', resolve(CurrencyRule::class),
                function ($input) {
                    return $input->price && $input->price > 0;
                });

            $validator->sometimes('price', ['required', new MoneyRule()],
                function ($input) {
                    return intval($input->status) === Offer::STATUS_AVAILABLE;
                });

            if ($requireImages) {
                $validator->sometimes('images', 'required', function ($input) {
                    $val = intval($input->status) === Offer::STATUS_AVAILABLE;

                    return $val;
                });
            }

            $validator->sometimes('images', 'file|image', function ($input) {
                $val = intval($input->status) === Offer::STATUS_AVAILABLE
                    && ! is_array($input->images);

                return $val;
            });

            $validator->sometimes('images.*', 'file|image', function ($input) {
                $val = intval($input->status) === Offer::STATUS_AVAILABLE
                    && is_array($input->images);

                return $val;
            });
        }

        return [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|min:5|max:2000',
            'currency' => '',
            'price' => '',
            'images' => '',
            'status' => Rule::in([
                Offer::STATUS_DRAFT,
                Offer::STATUS_AVAILABLE,
            ]),
        ];
    }

    /**
     * 'Bump' the offer - make it appear as new. Returns the success.
     *
     * @return boolean
     */
    public function bump()
    {
        if ($this->bumped_times < self::MAX_BUMP_TIMES) {
            ++$this->bumped_times;
            $this->listed_at = Carbon::now();

            return true;
        }

        return false;
    }

    /**
     * Report the offer to administrators
     *
     * @param integer $userId
     *
     * @return boolean
     */
    public function report($userId)
    {
        /** @var User|null $user */
        $user = $this->reportedBy()->where(['user_id' => $userId])->first();

        if ( ! $user) {
            ++$this->reported_times;
            $this->reportedBy()->attach($userId);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Mark the reported offer as appropriate
     *
     * @return boolean
     */
    public function markAppropriate()
    {
        $this->reported_times = 0;

        return true;
    }

    /**
     * Clear the list of users that have reported this offer
     *
     * @return boolean
     */
    public function resetAppropriateness()
    {
        return $this->reportedBy()->detach() > 0;
    }
}