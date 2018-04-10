<?php

namespace App\Auth;

use App\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

/**
 * {@see EloquentUserProvider} that asserts that the user's status is active
 * before allowing actions such as login.
 */
class UserProvider extends EloquentUserProvider
{
    const LOGIN_KEY = 'login';
    const EMAIL_KEY = 'email';
    const USERNAME_KEY = 'username';

    /**
     * @inheritDoc
     *
     * @param UserContract $user
     * @param array        $credentials
     *
     * @return bool
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
     *
     * @param array $credentials
     *
     * @return UserContract|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if ($credentials == null) {
            return null;
        }

        if (isset($credentials[self::LOGIN_KEY])) {
            $value = $credentials[self::LOGIN_KEY];
            $key   = $this->guessLoginKey($value);

            $credentials[$key] = $value;
            unset($credentials[self::LOGIN_KEY]);
        }

        return parent::retrieveByCredentials($credentials);
    }

    /**
     * Guess whether the provided login is an e-mail or a username.
     * @param $value
     *
     * @return string
     */
    protected function guessLoginKey($value)
    {
        return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false
            ? self::EMAIL_KEY : self::USERNAME_KEY);
    }

}