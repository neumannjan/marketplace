<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\DB\SearchRequest;
use App\Offer;
use Illuminate\Support\Collection;

class OfferSearchRequest extends SearchRequest
{
    protected $modelClass = Offer::class;
    protected $resourceClass = \App\Http\Resources\Offer::class;

    /**
     * @inheritDoc
     *
     * @param Offer $model
     */
    protected function filterResult($model, Collection $parameters)
    {
        return $model->displayable;
    }
}