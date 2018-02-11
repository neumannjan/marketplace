<?php

namespace App\Api\Request\DB;


use App\Api\Request\PaginatedRequest;
use App\Eloquent\Timestamp\TimestampPaginator;
use Illuminate\Support\Collection;

/**
 * An API request to retrieve a list of DB entries
 */
class MultiRequest extends PaginatedRequest
{
    use DBRequest;

    /**
     * MultiRequest constructor.
     * @param string $modelClass
     * @param string $resourceClass
     * @param bool $timestampBased
     */
    public function __construct($modelClass, $resourceClass, $timestampBased = false)
    {
        $this->modelClass = $modelClass;
        $this->resourceClass = $resourceClass;
        $this->timestampBased = $timestampBased;
    }

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
    protected function paginator(Collection $parameters, $perPage, $pageOrTimestamp)
    {
        $query = $this->buildQuery($parameters);

        if ($this->timestampBased) {
            return TimestampPaginator::fromQuery($query, $perPage, $pageOrTimestamp);
        } else {
            return $query->paginate($perPage, ['*'], 'page', $pageOrTimestamp);
        }
    }
}