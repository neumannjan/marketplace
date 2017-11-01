<?php

namespace App\Api;


use App\Api\Request\Request;
use App\Api\Response\Response;

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
    protected function doResolve($name, $parameters)
    {
        $n = $parameters['name'];
        return new Response($name, true, [
            'message' => "Bye $n!"
        ]);
    }
}