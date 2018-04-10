<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Illuminate\Http\Resources\MissingValue;

/**
 * LoadsAttributesByAuthorization - JSON resource helper trait with helper
 * functions.
 *
 * @package App\Http\Resources
 */
trait LoadsAttributesByAuthorization
{
    use ConditionallyLoadsAttributes;

    /**
     * Get currently authorized user
     *
     * @return \App\User|null
     */
    protected function getUser()
    {
        return \Auth::user();
    }

    /**
     * Retrieve a value based on whether the user has admin privileges
     *
     * @param mixed $value
     * @param mixed $default
     *
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenAdmin($value, $default = null)
    {
        $user = $this->getUser();

        return $this->when(
            $user && $user->is_admin,
            $value,
            func_num_args() === 2 ? $default : new MissingValue
        );
    }

    /**
     * Retrieve a value based on whether the user is logged in
     *
     * @param mixed $value
     * @param mixed $default
     *
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenLoggedIn($value, $default = null)
    {
        return $this->when(
            ! ! $this->getUser(),
            $value,
            func_num_args() === 2 ? $default : new MissingValue
        );
    }

    /**
     * Retrieve a value based on whether the model is owned by the logged in user
     *
     * @param mixed $value
     * @param mixed $default
     *
     * @return \Illuminate\Http\Resources\MissingValue|mixed
     */
    protected function whenOwned($value, $default = null)
    {
        $user = $this->getUser();

        return $this->when(
            $user && ($user->is_admin || $this->getOwnedBy($user)),
            $value,
            func_num_args() === 2 ? $default : new MissingValue
        );
    }

    /**
     * @param \App\User $user
     *
     * @return boolean
     */
    protected abstract function getOwnedBy(\App\User $user);
}