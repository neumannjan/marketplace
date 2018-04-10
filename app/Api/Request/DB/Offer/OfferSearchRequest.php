<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\DB\SearchRequest;
use App\Offer;
use Illuminate\Support\Collection;

/**
 * API request to search for offers
 *
 * @package App\Api\Request\DB\Offer
 */
class OfferSearchRequest extends SearchRequest
{
    protected $modelClass = Offer::class;
    protected $resourceClass = \App\Http\Resources\Offer::class;

    /**
     * @inheritDoc
     *
     * @param            $model
     * @param Collection $parameters
     *
     * @return mixed
     */
    protected function filterResult($model, Collection $parameters)
    {
        return $model->displayable;
    }
}