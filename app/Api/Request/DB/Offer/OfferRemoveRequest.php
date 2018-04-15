<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Offer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

/**
 * API request to remove an offer
 *
 * @package App\Api\Request\DB\Offer
 */
class OfferRemoveRequest extends Request
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
    protected function rules(
        Collection $parameters,
        Validator $validator = null
    )
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
        /** @var User $user */
        $user = $this->guard->user();

        $query = Offer::query()
            ->where(['id' => $parameters['id']]);

        if ( ! $user->is_admin) {
            $query = $query->where(['author_user_id' => $this->guard->id()]);
        }

        $offer = $query->first();

        if ($offer && $offer->delete() !== false) {
            return new Response(true, []);
        } else {
            return new Response(false, []);
        }
    }
}