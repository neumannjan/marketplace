<?php

namespace App\Api\Request\DB;


use App\Api\Request\PaginatedRequest;
use App\Offer;
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
            'author_username' => 'string',
            'order_by' => ['string'],
            'order' => ['string', Rule::in([self::ORDER_ASC, self::ORDER_DESC])]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function urlParameters()
    {
        return ['author_username', 'order_by', 'order'];
    }

    /**
     * @inheritDoc
     */
    protected function getPaginator(Collection $parameters, $perPage, $page)
    {
        /** @var Builder $query */
        $query = Offer::query();

        $query->active(); // Required so that users cannot see banned users or inactive offers

        // offer
        foreach ([] as $key) {
            if (($value = $parameters->get($key)) !== null) {
                $query->where([$key => $value]);
            }
        }

        // author
        $query->whereHas('author', function (Builder $query) use ($parameters) {
            foreach (['username'] as $key) {
                if (($value = $parameters->get("author_$key")) !== null) {
                    $query->where([$key => $value]);
                }
            }
        });

        // order
        $orderBy = $parameters->get('order_by', 'listed_at');
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