<?php

namespace App\Api\Request\DB;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BasicMultiRequest extends MultiRequest
{

    /**
     * @var string
     */
    protected $resourceClass;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * BasicMultiRequest constructor.
     * @param string $modelClass
     * @param string $resourceClass
     */
    public function __construct($modelClass, $resourceClass)
    {
        $this->resourceClass = $resourceClass;
        $this->modelClass = $modelClass;
    }

    /**
     * @inheritDoc
     */
    protected function resourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * @inheritDoc
     */
    protected function modelClass()
    {
        return $this->modelClass;
    }

    /**
     * @inheritDoc
     */
    protected function additionalQuery(Builder $query, Collection $parameters)
    {
        foreach ($this->getDBParameters($parameters) as $key => $value) {
            $this->addWhere($query, $key, '=', $value);
        }

        return $query;
    }
}