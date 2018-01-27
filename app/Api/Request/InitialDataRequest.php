<?php

namespace App\Api\Request;

/**
 * Request that contains variables that the frontend might require at the beginning of its existence.
 * Extends {@see GlobalRequest}.
 */
class InitialDataRequest extends GlobalDataRequest
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
                'numeric' => trans('validation.numeric'),
                'containsNumeric' => trans('validation.contains.numeric'),
                'containsNonNumeric' => trans('validation.contains.non_numeric'),
                'confirmed' => trans('validation.confirmed'),
                'email' => trans('validation.email'),
            ]
        ];

        return $array;
    }
}
