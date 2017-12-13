<?php

namespace App\Api\Request\DB;


use App\Api\Request\PaginatedRequest;
use App\Offer;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class OfferRequest extends PaginatedRequest
{

    /**
     * @inheritDoc
     */
    protected function rules()
    {
        return [
            'status' => ['integer', Rule::in([Offer::STATUS_INACTIVE, Offer::STATUS_AVAILABLE, Offer::STATUS_SOLD])]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function urlParameters()
    {
        return ['status'];
    }

    /**
     * @inheritDoc
     */
    protected function getPaginator(Collection $parameters, $perPage, $page)
    {
        $query = Offer::query();

        foreach (['status'] as $key) {
            if (($value = $parameters->get($key)) !== null) {
                $query->where([$key => $value]);
            }
        }

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