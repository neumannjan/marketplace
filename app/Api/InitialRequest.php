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
                'minLength' => trans('validation.min.string'),
                'required' => trans('validation.required')
            ]
        ];

        return $array;
    }
}