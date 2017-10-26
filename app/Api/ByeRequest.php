<?php

namespace App\Api;


use App\Api\Request\Request;

class ByeRequest extends Request
{
    /**
     * @inheritDoc
     */
    protected function rules()
    {
        return [
            'name' => 'required|string'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($parameters)
    {
        $name = $parameters['name'];
        return [
            'message' => "Bye $name!"
        ];
    }
}