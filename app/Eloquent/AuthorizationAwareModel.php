<?php

namespace App\Eloquent;


use App\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\NotIn;

interface AuthorizationAwareModel
{
    /**
     * Returns a list of scopes that can be used from outside of the app
     * @return string[]
     */
    public function getPublicScopes();

    /**
     * Returns whether a certain user can use a particular scope
     * @param string $scopeName
     * @param User $user
     * @return bool
     */
    public function canUsePublicScope($scopeName, User $user = null);

    /**
     * Determines whether the user is allowed to additionally limit his query with provided column names when requesting
     * a particular scope
     * @param string $scopeName
     * @param string[]|Collection $columnNames
     * @return In|NotIn
     */
    public function validatePublicScopeParams($scopeName, $columnNames);
}