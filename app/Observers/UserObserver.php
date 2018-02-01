<?php

namespace App\Observers;


use App\User;

/**
 * Events for the {@see \App\User User} model
 */
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

    /**
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    function deleting(User $user)
    {
        // delete offers (to dispatch appropriate offer delete events)
        $offers = $user->offers;
        foreach ($user->offers as $offer) {
            $offer->delete();
        }

        // delete profile image (to dispatch appropriate image delete events)
        $user->profile_image->delete();

        return true;
    }
}