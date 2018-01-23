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
        return $query
            ->where('status', Offer::STATUS_AVAILABLE);
    }

    /**
     * @inheritDoc
     */
    protected function filterResults(\Illuminate\Database\Eloquent\Collection $results)
    {
        return $results
            ->where('displayable', '=', true);
    }
}