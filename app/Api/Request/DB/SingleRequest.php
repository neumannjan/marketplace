<?php

namespace App\Api\Request\DB;


use App\Api\Request\Request;
use App\Api\Response\Response;
use Illuminate\Support\Collection;

/**
 * An API request to retrieve a single DB entry
 */
class SingleRequest extends Request
{
    use DBRequest;

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $query = $this->buildQuery($parameters);

        $model = $query->first();

        if (!$model) {
            return new Response(true, null);
        }

        /** @var \Illuminate\Http\Resources\Json\Resource $resourceClass */
        $resourceClass = $this->resourceClass($parameters);

        return new Response(true, ($resourceClass)::make($model));
    }
}