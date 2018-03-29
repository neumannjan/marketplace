<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Offer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class OfferEditRequest extends Request
{
    use ProcessImages;

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
    protected function rules(Validator $validator = null)
    {
        return [
                'id' => 'required|integer',
                'imageOrder' => 'required|array',
                'imageOrder.*.new' => 'required|boolean',
                'imageOrder.*.id' => 'required|integer',
            ] + Offer::getValidationRules($validator, false);
    }

    /**
     * @inheritDoc
     */
    protected function jsonParameters()
    {
        return ['imageOrder'];
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        /** @var User $user */
        $user = $this->guard->user();

        $query = Offer::query()
            ->where(['id' => $parameters['id']]);

        if (!$user->is_admin) {
            $query = $query->where(['author_user_id' => $this->guard->id()]);
        }

        $offer = $query->first();

        if ($offer) {
            /** @var Offer $offer */
            $offer->fill([
                'name' => $parameters['name'],
                'description' => $parameters->get('description'),
                'price_value' => $parameters->get('price', 0),
                'currency' => $parameters->get('currency'),
            ]);

            $offer->save();

            $this->processImages($offer, $parameters['imageOrder'], $parameters->get('images'), true);

            return new Response(true, ['id' => $offer->id]);
        }
    }

}