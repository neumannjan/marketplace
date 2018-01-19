<?php

namespace App\Api\Request\DB;


use App\Api\Request\PaginatedRequest;
use Illuminate\Support\Collection;

/**
 * An API request to retrieve a list of DB entries
 */
abstract class MultiRequest extends PaginatedRequest
{
    use DBRequest;

    /**
     * @inheritDoc
     */
    protected function urlParameters(Collection $parameters)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function _urlParameters(Collection $parameters)
    {

        $result = array_merge([
            'scope'
        ], $this->getDBParameters($parameters)->keys()->toArray(), parent::_urlParameters($parameters));

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function paginator(Collection $parameters, $perPage, $page)
    {
        $query = $this->buildQuery($parameters);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
}