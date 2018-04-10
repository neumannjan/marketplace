<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Offer;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

/**
 * API request to report an offer
 *
 * @package App\Api\Request\DB\Offer
 */
class OfferReportRequest extends Request
{

    /** @var Guard */
    protected $guard;

    /**
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
     *
     * @param Validator|null $validator
     *
     * @return array
     */
    protected function rules(Validator $validator = null)
    {
        return [
            'id' => 'required|integer',
        ];
    }

    /**
     * @inheritDoc
     *
     * @param            $name
     * @param Collection $parameters
     *
     * @return Response
     */
    protected function doResolve($name, Collection $parameters)
    {
        /** @var Offer $offer */
        $offer = Offer::query()->where([
            'id' => $parameters['id'],
            'status' => Offer::STATUS_AVAILABLE,
        ])->first();

        if ($offer && $offer->report($this->guard->id()) && $offer->save()) {
            return new Response(true, []);
        } else {
            return new Response(false, []);
        }
    }
}