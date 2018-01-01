<?php

namespace App\Api\Request\DB;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Offer;
use App\User;
use Illuminate\Database\Eloquent\Builder;
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
        ];
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $params = $parameters->all();
        $type = $params['type'];
        unset($params['type']);

        $model = self::TYPE_MODEL_MAP[$type];

        /** @var Builder $query */
        $query = $model::query();

        $query->active(); // Required so that users cannot see banned users or inactive offers

        $model = $query->where($params)->first();

        if (!$model) {
            return new Response(true, null);
        }

        $resource = self::TYPE_RESOURCE_MAP[$type];

        return new Response(true, ($resource)::make($model));
    }
}