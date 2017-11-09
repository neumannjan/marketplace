<?php

namespace App\Api;

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
                'email' => trans('validation.email'),
                'confirmed' => trans('validation.confirmed'),
                'slug' => trans('validation.slug'),
            ]
        ];

        return $array;
    }
}