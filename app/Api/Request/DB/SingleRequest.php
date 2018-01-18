<?php

namespace App\Api\Request\DB;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Eloquent\AuthorizationAwareModel;
use App\Offer;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class SingleRequest extends Request
{

    const TYPE_MODEL_MAP = [
        'offer' => Offer::class,
        'user' => User::class
    ];

    const TYPE_RESOURCE_MAP = [
        'offer' => \App\Http\Resources\Offer::class,
        'user' => \App\Http\Resources\User::class
    ];

    /**
     * @inheritDoc
     */
    protected function rules()
    {
        return [
            'type' => ['string', 'required', Rule::in(array_keys(self::TYPE_MODEL_MAP))],
            'scope' => ['string', 'required']
        ];
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $params = $parameters->all();
        $type = $params['type'];
        $scope = $params['scope'];
        unset($params['type']);
        unset($params['scope']);

        $modelClass = self::TYPE_MODEL_MAP[$type];
        /** @var AuthorizationAwareModel|Model $model */
        $model = new $modelClass;

        // Validate passed data
        \Validator::validate([
            'scope' => $scope,
            'params' => $params
        ], [
            'scope' => Rule::in($model->getPublicScopes()),
            'params' => 'required'
        ], [
            'required' => 'An identifier such as ID is required'
        ]);

        // Check if the user can access this scope
        if (!$model->canUsePublicScope($scope, \Auth::user())) {
            $this->authorizationError();
        }

        // Validate passed query parameters
        if (!$model->validatePublicScopeParams($scope, array_keys($params))) {
            $this->authorizationError();
        }

        $query = $model->newQuery();

        // Set the scope
        $query->scopes([$scope]);

        // Limit the model further
        $query->where($params);

        // Retrieve model
        $model = $query->first();

        if (!$model) {
            return new Response(true, null);
        }

        $resource = self::TYPE_RESOURCE_MAP[$type];

        return new Response(true, ($resource)::make($model));
    }
}