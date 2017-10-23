<?php

namespace App\Observers;


use App\User;

class UserObserver
{
    public function creating(User $user)
    {
        $user->activation_token = str_random(User::ACTIVATION_TOKEN_LENGTH);
    }

    public function saving(User $user)
    {
        if ($user->display_name == $user->username) {
            $user->display_name = null;
        }
    }
}