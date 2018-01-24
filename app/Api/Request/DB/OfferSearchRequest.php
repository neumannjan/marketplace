<?php

namespace App\Api\Request\DB;


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