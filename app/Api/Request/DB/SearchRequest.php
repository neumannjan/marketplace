<?php

namespace App\Api\Request\DB;


use App\Api\Request\PaginatedRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

/**
 * An API request to retrieve a list of DB entries based on a search query
 */
abstract class SearchRequest extends PaginatedRequest
{
    use BasicDBRequest;

    public function __construct()
    {
        // empty constructor. Should not have the `modelClass` and `resourceClass` parameters.
    }


    /**
     * @inheritDoc
     */
    protected function rules(Validator $validator = null)
    {
        return [
            'query' => 'required|string'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function urlParameters(Collection $parameters)
    {
        return $this
            ->getDBParameters($parameters)
            ->keys()
            ->push('query')
            ->toArray();
    }

    /**
     * Filters the query
     * @param Builder $query
     * @return Builder
     */
    protected abstract function filterQuery(Builder $query);

    /**
     * @inheritDoc
     */
    protected function paginator(Collection $parameters, $perPage, $pageOrTimestamp)
    {
        /** @var Model|Searchable $modelClass */
        $modelClass = $this->modelClass();

        $query = $modelClass::search($parameters['query']);
        $query = $this->filterQuery($query);

        return $query->paginate($perPage, 'page', $pageOrTimestamp);
    }

}