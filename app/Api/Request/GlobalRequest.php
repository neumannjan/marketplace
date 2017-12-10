<?php

namespace App\Api\Request;


use App\Api\Data\FlashMessages;
use App\Api\Response\Response;
use Illuminate\Support\Collection;

/**
 * Request that contains global variables that the frontend might request repeatedly.
 */
class GlobalRequest extends Request
{
    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        return new Response(true, static::get());
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

        $flashMessages = new FlashMessages();
        $flashMessages->clearSession();

        $flash = $flashMessages->toPassable();

        return compact(
            'token',
            'is_authenticated',
            'flash'
        );
    }
}