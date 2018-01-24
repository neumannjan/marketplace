<?php

namespace App\Api\Request;

use App\Facades\Money;
use Money\Currency;

/**
 * Request that contains variables that the frontend might require at the beginning of its existence.
 * Extends {@see GlobalRequest}.
 */
class InitialRequest extends GlobalRequest
{
    public static function get()
    {
        $array = parent::get();

        $array['messages'] = [
            'validation' => [
                'min' => trans('validation.min.string'),
                'max' => trans('validation.max.string'),
                'required' => trans('validation.required'),
                'slug' => trans('validation.slug'),
                'numeric' => trans('validation.contains.numeric'),
                'nonNumeric' => trans('validation.contains.non_numeric'),
                'confirmed' => trans('validation.confirmed'),
                'email' => trans('validation.email'),
            ]
        ];

        $array['currencies'] = \Cache::rememberForever('currencyArray', function () {
            $currencyContainer = Money::getCurrencies();
            $iterator = $currencyContainer->getIterator();

            $currencies = [];

            /** @var Currency $currency */
            foreach ($iterator as $currency) {
                $currencies[] = [$currency->getCode(), $currencyContainer->subunitFor($currency)];
            }

            return $currencies;
        });

        return $array;
    }
}
