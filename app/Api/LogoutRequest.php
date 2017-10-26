<?php

namespace App\Api;


use App\Api\Request\Request;

class LogoutRequest extends Request
{

    /**
     * @inheritDoc
     */
    protected function doResolve($parameters)
    {
        \Auth::logout();

        //TODO are these lines (and token passing) necessary ?
        \Session::invalidate();
        \Session::regenerateToken();

        return [
            'token' => csrf_token()
        ];
    }
}