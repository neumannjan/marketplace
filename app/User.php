<?php

namespace App;

use App\Eloquent\AuthorizationAwareModel;
use App\Notifications\ActivateRegistration;
use App\Notifications\ResetPassword;
use App\Observers\UserObserver;
use App\Rules\ContainsNonNumericRule;
use App\Rules\ContainsNumericRule;
use App\Rules\SlugRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

/**
 * User model. Manages users in the database.
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $activation_token
 * @property string $display_name
 * @property int $is_admin
 * @property array $options
 * @property int $status 0 == inactive, 1 == active, 2 == banned
 * @property string|null $description
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $profile_image_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Offer[] $bought
 * @property-read string $locale
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Offer[] $offers
 * @property-read \App\Image|null $profile_image
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User banned()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User public()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User unlimited()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User toBeRemoved()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActivationToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereProfileImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements AuthorizationAwareModel
{
    use Notifiable, Searchable;

    const ACTIVATION_TOKEN_LENGTH = 32;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = 2;

    const SCOPE_PUBLIC = 'public';
    const SCOPE_UNLIMITED = 'unlimited';
    const SCOPE_BANNED = 'banned';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable
        = [
            'username',
            'email',
            'password',
            'activation_token',
            'display_name',
            'status',
        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden
        = [
            'password',
            'remember_token',
        ];

    protected $with
        = [
            'profile_image',
        ];

    protected $casts
        = [
            'options' => 'array',
        ];

    /**
     * @inheritDoc
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(UserObserver::class);
    }

    /**
     * Get a display name or fallback to the username if not provided.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if ($this->attributes['display_name'] == null) {
            return $this->attributes['username'];
        }

        return $this->attributes['display_name'];
    }

    /**
     * Get the user's preferred locale
     *
     * @return string
     */
    public function getLocaleAttribute()
    {
        return isset($this->options['locale']) ? $this->options['locale']
            : config('app.fallback_locale');
    }

    /**
     * @inheritDoc
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the registration activation e-mail notification.
     */
    public function sendRegistrationActivateNotification()
    {
        $this->notify(new ActivateRegistration($this->activation_token,
            $this->username, $this->email));
    }

    /**
     * Sets the user's status to active.
     * Returns false if the user is banned, already active, or if `$token` does not match the user's `activation_token`.
     *
     * @var string|null $token If
     * @return bool
     */
    public function activate($token = null)
    {
        if ($token !== null && strcmp($token, $this->activation_token) !== 0) {
            return false;
        }

        if ($this->status == self::STATUS_INACTIVE) {
            $this->status = self::STATUS_ACTIVE;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Get validation rules for a creation request.
     *
     * @param bool $updating
     *
     * @return array
     */
    public static function getValidationRules($updating = false)
    {
        $rules = [
            'password' => [
                $updating ? 'sometimes' : 'required',
                'string',
                'min:8',
                new ContainsNumericRule(),
                new ContainsNonNumericRule(),
            ],
            'display_name' => ['nullable', 'string', 'max:20'],
        ];

        if ( ! $updating) {
            $rules['username'] = [
                'required',
                'string',
                'min:4',
                'max:20',
                'unique:users',
                new SlugRule(),
            ];
            $rules['email']    = [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
            ];
        } else {
            $rules['email'] = ['required', 'string', 'email', 'max:255'];
        }

        return $rules;
    }

    /**
     * Relation to the offers owned by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function offers()
    {
        return $this->hasMany(Offer::class, 'author_user_id');
    }

    /**
     * Relation to the offers bought by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bought()
    {
        return $this->hasMany(Offer::class, 'sold_to_user_id');
    }

    /**
     * Relation to the profile image
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profile_image()
    {
        return $this->belongsTo(Image::class, 'profile_image_id');
    }

    /**
     * Returns the date that inactive users have to be newer than
     * to not be removed
     *
     * @return Carbon
     */
    public function inactiveRemovedFromTimestamp()
    {
        return Carbon::now()->subDays(7);
    }

    /**
     * Returns the date that banned users have to be newer than
     * to not be removed
     *
     * @return Carbon
     */
    public function bannedRemovedFromTimestamp()
    {
        return Carbon::now()->subMonths(6);
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
        return $query
            ->whereNested(function ($query) {
                /** @var \Illuminate\Database\Query\Builder $query */
                return $query
                    ->where(['status' => self::STATUS_INACTIVE])
                    ->whereDate('updated_at', '<',
                        $this->inactiveRemovedFromTimestamp());
            })
            ->whereNested(function ($query) {
                /** @var \Illuminate\Database\Query\Builder $query */
                return $query
                    ->where(['status' => self::STATUS_BANNED])
                    ->whereDate('updated_at', '<',
                        $this->bannedRemovedFromTimestamp());
            }, 'or');
    }

    /**
     * Limits the query to only return users that are active
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopePublic(Builder $query)
    {
        return $query
            ->where(['status' => self::STATUS_ACTIVE]);
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
     * Limits the query to only return users that are banned
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeBanned(Builder $query)
    {
        return $query
            ->where(['status' => self::STATUS_BANNED]);
    }

    /**
     * @inheritDoc
     */
    public function getPublicScopes()
    {
        return [self::SCOPE_PUBLIC, self::SCOPE_UNLIMITED, self::SCOPE_BANNED];
    }

    /**
     * @inheritDoc
     *
     * @param string    $scopeName
     * @param User|null $user
     *
     * @return bool
     */
    public function canUsePublicScope($scopeName, User $user = null)
    {
        switch ($scopeName) {
            case self::SCOPE_PUBLIC:
                return true;
            case self::SCOPE_UNLIMITED:
            case self::SCOPE_BANNED:
                return $user->is_admin ? true : false;
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @param string   $scopeName
     * @param string[] $columnNames
     *
     * @return bool
     */
    public function validatePublicScopeParams($scopeName, $columnNames)
    {
        switch ($scopeName) {
            case self::SCOPE_PUBLIC:
                return Collection::wrap($columnNames)
                    ->diff(Collection::make(['id', 'username', 'email']))
                    ->isEmpty();
            case self::SCOPE_UNLIMITED:
            case self::SCOPE_BANNED:
                return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function toSearchableArray()
    {
        return Arr::only($this->toArray(),
            ['id', 'username', 'email', 'description', 'display_name']);
    }

}
