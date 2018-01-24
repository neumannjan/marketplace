<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\DB\SearchRequest;
use App\Offer;
use Laravel\Scout\Builder;

class OfferSearchRequest extends SearchRequest
{
    protected $modelClass = Offer::class;
    protected $resourceClass = \App\Http\Resources\Offer::class;

    /**
     * @inheritDoc
     */
    protected function filterQuery(Builder $query)
    {
        return $query->where('displayable', true);
    }
}