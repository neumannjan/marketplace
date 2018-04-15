<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\DB\MultiRequest;
use App\Offer;
use Illuminate\Support\Collection;

/**
 * API request to fetch offers based on a named scope
 *
 * @package App\Api\Request\DB\Chat
 */
class OffersRequest extends MultiRequest
{
    public $modelClass = Offer::class;
    public $resourceClass = \App\Http\Resources\Offer::class;

    /**
     * OffersRequest constructor.
     */
    public function __construct()
    {
        parent::__construct($this->modelClass, $this->resourceClass);
    }

    /**
     * @inheritDoc
     *
     * @param Collection $parameters
     */
    protected function isOrderBased(Collection $parameters)
    {
        $scope = $parameters->get('scope', $this->defaultScope);

        // TODO fix AfterPaginator for reported offers?
        return $scope !== Offer::SCOPE_REPORTED;
    }


}