<?php

namespace App\Api\Request;


use App\Api\Data\FlashMessages;
use App\Api\Response\Response;
use App\Http\Resources\User;
use Illuminate\Support\Collection;

/**
 * Request that contains global variables that the frontend might request repeatedly.
 */
class GlobalDataRequest extends Request
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

        // locale
        $locale = \App::getLocale();

        // authentication

        $is_authenticated = \Auth::check();
        $user = $is_authenticated ? new User(\Auth::user()) : null;

        // is admin
        $is_admin = $is_authenticated && \Auth::user()->is_admin ? true : false;

        // flash messages

        $flashMessages = new FlashMessages();
        $flashMessages->clearSession();

        $flash = $flashMessages->toPassable();

        return compact(
            'token',
            'locale',
            'is_authenticated',
            'user',
            'is_admin',
            'flash'
        );
    }
}