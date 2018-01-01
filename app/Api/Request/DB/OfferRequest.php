<?php

namespace App\Api\Request\DB;


use App\Api\Request\PaginatedRequest;
use App\Offer;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class OfferRequest extends PaginatedRequest
{

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    /**
     * @inheritDoc
     */
    protected function rules()
    {
        return [
            'status' => ['integer', Rule::in([Offer::STATUS_INACTIVE, Offer::STATUS_AVAILABLE, Offer::STATUS_SOLD])],
            'author_status' => ['integer', Rule::in([User::STATUS_INACTIVE, User::STATUS_ACTIVE, User::STATUS_BANNED])],
            'order_by' => ['string'],
            'order' => ['string', Rule::in([self::ORDER_ASC, self::ORDER_DESC])]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function urlParameters()
    {
        return ['status', 'author_status', 'order_by', 'order'];
    }

    /**
     * @inheritDoc
     */
    protected function getPaginator(Collection $parameters, $perPage, $page)
    {
        $query = Offer::query();

        // offer
        foreach (['status'] as $key) {
            if (($value = $parameters->get($key)) !== null) {
                $query->where([$key => $value]);
            }
        }

        // author
        $query->whereHas('author', function (Builder $query) use ($parameters) {
            foreach (['status'] as $key) {
                if (($value = $parameters->get("author_$key")) !== null) {
                    $query->where([$key => $value]);
                }
            }
        });

        // order
        $orderBy = $parameters->get('order_by', 'created_at'); //TODO add bumpable timestamp
        $order = $parameters->get('order', self::ORDER_DESC);

        $query->orderBy($orderBy, $order);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @inheritDoc
     */
    protected function getResourceClass()
    {
        return \App\Http\Resources\Offer::class;
    }
}