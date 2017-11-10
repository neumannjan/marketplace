<?php

namespace App\Api\Request\Auth;


use App\Api\Request\Request;
use App\Api\Response\Response;

/**
 * API logout request
 */
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