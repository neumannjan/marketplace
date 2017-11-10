<?php

namespace App\Api\Request;

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
                'required' => trans('validation.required'),
                'slug' => trans('validation.slug'), //TODO letters, numbers
                'numeric' => trans('validation.contains.numeric'),
                'nonNumeric' => trans('validation.contains.non_numeric'),
                'confirmed' => trans('validation.confirmed'),
                'email' => trans('validation.email'),
            ]
        ];

        return $array;
    }
}
