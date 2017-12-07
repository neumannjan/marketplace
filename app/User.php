<?php

namespace App;

use App\Notifications\ActivateRegistration;
use App\Notifications\ResetPassword;
use App\Offer;
use App\Rules\ContainsNonNumericRule;
use App\Rules\ContainsNumericRule;
use App\Rules\SlugRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;

/**
 * User model. Manages users in the database.
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $activation_token
 * @property string|null $display_name
 * @property array $options
 * @property int $status 0 == inactive, 1 == active, 2 == banned
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @method static Builder|User whereActivationToken($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDisplayName($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereOptions($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsername($value)
 * @mixin \Eloquent
 * @property-read Collection|Offer[] $bought
 * @property-read Collection|Offer[] $offers
 */
class User extends Authenticatable
{
    use Notifiable;

    const ACTIVATION_TOKEN_LENGTH = 32;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = 2;

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
        return $this->hasMany(Offer::class, 'author');
    }

    public function bought()
    {
        return $this->hasMany(Offer::class, 'sold_to');
    }
}
