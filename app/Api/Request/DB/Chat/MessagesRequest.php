<?php

namespace App\Api\Request\DB\Chat;


use App\Api\Request\DB\MultiRequest;
use App\Message;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class MessagesRequest extends MultiRequest
{
    protected $modelClass = Message::class;

    protected $defaultScope = Message::SCOPE_PERSONAL;

    protected $orderBased = true;

    protected $perPageDefault = 15;

    /**
     * @var Guard
     */
    protected $guard;

    /**
     * MessagesRequest constructor.
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        parent::__construct($this->modelClass, $this->resourceClass, $this->orderBased);
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        return $this->guard->check();
    }

    /**
     * @inheritDoc
     */
    protected function rules(Validator $validator = null)
    {
        return [
            'with' => 'sometimes|string'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function urlParameters(Collection $parameters)
    {
        return ['with'];
    }

    /**
     * @inheritDoc
     */
    protected function additionalQuery($query, Collection $parameters)
    {
        parent::additionalQuery($query, $parameters);

        $with = $parameters['with'];

        return $query->whereNested(function ($query) use ($with) {
            /** @var Builder $query */
            return $query
                ->where(['to_username' => $with])
                ->orWhere(['from_username' => $with]);
        });
    }

    /**
     * @inheritDoc
     */
    protected function resourceClass(Collection $parameters)
    {
        return \App\Http\Resources\Message::class;
    }
}