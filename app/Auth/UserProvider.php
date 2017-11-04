<?php

namespace App\Auth;

use App\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

/**
 * {@see EloquentUserProvider} that asserts that the user's status is active.
 */
class UserProvider extends EloquentUserProvider
{
    const LOGIN_KEY = 'login';
    const EMAIL_KEY = 'email';
    const USERNAME_KEY = 'username';

    /**
     * @inheritDoc
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        if ($user instanceof User) {
            if ($user->status != User::STATUS_ACTIVE) {
                return false;
            }
        }

        return parent::validateCredentials($user, $credentials);
    }

    /**
     * @inheritDoc
     */
    public function retrieveByCredentials(array $credentials)
    {
        if ($credentials == null) {
            return null;
        }

        if (isset($credentials[self::LOGIN_KEY])) {
            $value = $credentials[self::LOGIN_KEY];
            $key = $this->guessLoginKey($value);

            $credentials[$key] = $value;
            unset($credentials[self::LOGIN_KEY]);
        }

        return parent::retrieveByCredentials($credentials);
    }

    protected function guessLoginKey($value)
    {
        return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? self::EMAIL_KEY : self::USERNAME_KEY);
    }

}