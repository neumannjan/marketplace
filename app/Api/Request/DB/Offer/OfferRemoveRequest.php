<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Offer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

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
     */
    protected function rules(Validator $validator = null)
    {
        return [
            'id' => 'required|integer'
        ];
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    protected function doResolve($name, Collection $parameters)
    {
        /** @var User $user */
        $user = $this->guard->user();

        /** @var Offer $offer */
        $offer = Offer::query()->where([
            'id' => $parameters['id'],
            'author_user_id' => $user->id
        ])->first(['id', 'name']);

        if($offer && $offer->delete() !== false) {
            return new Response(true, []);
        } else {
            return new Response(false, []);
        }
    }
}