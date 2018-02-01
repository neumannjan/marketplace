<?php

namespace App;

use App\Eloquent\AuthorizationAwareModel;
use App\Notifications\ActivateRegistration;
use App\Notifications\ResetPassword;
use App\Observers\UserObserver;
use App\Rules\ContainsNonNumericRule;
use App\Rules\ContainsNumericRule;
use App\Rules\SlugRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * User model. Manages users in the database.
 */
class User extends Authenticatable implements AuthorizationAwareModel
{
    use Notifiable;

    const ACTIVATION_TOKEN_LENGTH = 32;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = 2;

    const SCOPE_PUBLIC = 'public';
    const SCOPE_UNLIMITED = 'unlimited';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'activation_token',
        'display_name',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'options' => 'array'
    ];

    /**
     * @inheritDoc
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(UserObserver::class);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->attributes['display_name'] == null) {
            return $this->attributes['username'];
        }

        return $this->attributes['display_name'];
    }

    /**
     * @inheritDoc
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function sendRegistrationActivateNotification()
    {
        $this->notify(new ActivateRegistration($this->activation_token, $this->username, $this->email));
    }

    /**
     * Sets the user's status to active.
     * Returns false if the user is banned, already active, or if `$token` does not match the user's `activation_token`.
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
     * @return array
     */
    public static function getValidationRules()
    {
        return [
            'username' => ['required', 'string', 'min:5', 'max:255', 'unique:users', new SlugRule()],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', new ContainsNumericRule(), new ContainsNonNumericRule()],
            'display_name' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'author_user_id');
    }

    public function bought()
    {
        return $this->hasMany(Offer::class, 'sold_to_user_id');
    }

    public function profile_image()
    {
        return $this->belongsTo(Image::class, 'profile_image_id');
    }

    /**
     * Limits the query to only return users that are active
     * @param Builder $query
     * @return Builder
     */
    public function scopePublic(Builder $query)
    {
        return $query
            ->where(['status' => self::STATUS_ACTIVE]);
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
     * @inheritDoc
     */
    public function getPublicScopes()
    {
        return [self::SCOPE_PUBLIC, self::SCOPE_UNLIMITED];
    }

    /**
     * @inheritDoc
     */
    public function canUsePublicScope($scopeName, User $user = null)
    {
        switch ($scopeName) {
            case self::SCOPE_PUBLIC:
                return true;
            case self::SCOPE_UNLIMITED:
                return $user->is_admin ? true : false;
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
                    ->diff(Collection::make(['id', 'username', 'email']))
                    ->isEmpty();
            case self::SCOPE_UNLIMITED:
                return true;
        }

        return false;
    }


}
