<?php

namespace App\Api\Request\DB\User;


use App\Api\Request\DB\SearchRequest;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserSearchRequest extends SearchRequest
{
    protected $modelClass = User::class;
    protected $resourceClass = \App\Http\Resources\User::class;

    /** @var Guard */
    protected $guard;

    /**
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        /** @var User | null $user */
        $user = $this->guard->user();

        return $user && $user->is_admin;
    }

    /**
     * @inheritDoc
     */
    protected function urlParameters(Collection $parameters)
    {
        return [
            'status' => 'sometimes|integer'
        ];
    }

    /**
     * @param User $model
     * @inheritDoc
     */
    protected function filterResult($model, Collection $parameters)
    {
        return $parameters->has('status') ? $model->status === $parameters['status'] : true;
    }
}