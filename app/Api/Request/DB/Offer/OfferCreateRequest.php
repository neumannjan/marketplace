<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Offer;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;

class OfferCreateRequest extends Request
{

    /** @var Guard */
    protected $guard;

    /**
     * OfferCreateRequest constructor.
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        return $this->guard->check();
    }

    /**
     * @inheritDoc
     */
    protected function rules()
    {
        return Offer::getValidationRules();
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        return new Response(true, []);
    }

}