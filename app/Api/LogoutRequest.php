<?php

namespace App\Api;


use App\Api\Request\Request;
use App\Api\Response\Response;

class LogoutRequest extends Request
{

    /**
     * @inheritDoc
     */
    protected function doResolve($name, $parameters)
    {
        \Auth::logout();

        \Session::invalidate();

        return new Response($name, true, []);
    }
}