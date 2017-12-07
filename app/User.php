<?php

namespace App;

use App\Notifications\ActivateRegistration;
use App\Notifications\ResetPassword;
use App\Rules\ContainsNonNumericRule;
use App\Rules\ContainsNumericRule;
use App\Rules\SlugRule;
use Illuminate\Foundation\Auth\User as Authenticatable;
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
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Offer[] $bought
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Offer[] $offers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActivationToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 * @mixin \Eloquent
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
        return $this->hasMany(Offer::class, 'author_user_id');
    }

    public function bought()
    {
        return $this->hasMany(Offer::class, 'sold_to_user_id');
    }
}
