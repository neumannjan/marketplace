<?php

namespace App\Api;


use App\Api\Request\Request;
use App\Api\Response\Response;

class GlobalRequest extends Request
{
    /**
     * @inheritDoc
     */
    protected function doResolve($name, $parameters)
    {
        return new Response($name, true, self::get());
    }

    public static final function get()
    {
        $token = csrf_token();

        if ($token == null) {
            \Session::regenerateToken();
            $token = csrf_token();
        }

        $is_authenticated = \Auth::check();

        return compact('token', 'is_authenticated');
    }
}