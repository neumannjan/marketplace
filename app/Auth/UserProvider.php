<?php

namespace App\Auth;

use App\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

/**
 * TODO replace with middleware
 */
class UserProvider extends EloquentUserProvider
{
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

}