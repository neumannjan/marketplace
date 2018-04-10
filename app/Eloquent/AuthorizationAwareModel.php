<?php

namespace App\Eloquent;


use App\User;
use Illuminate\Support\Collection;

/**
 * An interface for models to give them the ability to scope database queries
 * based on current user authentication.
 *
 * @package App\Eloquent
 */
interface AuthorizationAwareModel
{
    /**
     * Returns a list of scopes that can be used from outside of the app
     *
     * @return string[]
     */
    public function getPublicScopes();

    /**
     * Returns whether a certain user can use a particular scope
     *
     * @param string $scopeName
     * @param User   $user
     *
     * @return bool
     */
    public function canUsePublicScope($scopeName, User $user = null);

    /**
     * Determines whether the user is allowed to additionally limit his query with provided column names when requesting
     * a particular scope
     *
     * @param string              $scopeName
     * @param string[]|Collection $columnNames
     *
     * @return bool
     */
    public function validatePublicScopeParams($scopeName, $columnNames);
}