<?php

namespace App\Api;


use App\Api\Data\FlashMessages;
use App\Api\Request\Request;
use App\Api\Response\Response;

/**
 * Request that contains global variables that the frontend might request repeatedly.
 */
class GlobalRequest extends Request
{
    /**
     * @inheritDoc
     */
    protected function doResolve($name, $parameters)
    {
        return new Response($name, true, static::get());
    }

    public static function get()
    {
        // CSRF token
        $token = csrf_token();

        if ($token == null) {
            \Session::regenerateToken();
            $token = csrf_token();
        }

        // authentication

        $is_authenticated = \Auth::check();

        // flash messages

        $flash = (new FlashMessages())->toPassable();

        return compact(
            'token',
            'is_authenticated',
            'flash'
        );
    }
}