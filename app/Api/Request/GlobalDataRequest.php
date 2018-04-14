<?php

namespace App\Api\Request;


use App\Api\Data\FlashMessages;
use App\Api\Response\Response;
use App\Http\Resources\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Request that contains global variables that the frontend might request
 * repeatedly.
 */
class GlobalDataRequest extends Request
{
    /**
     * @inheritdoc
     *
     * @param string     $name
     * @param Collection $parameters
     *
     * @return Response
     */
    protected function doResolve($name, Collection $parameters)
    {
        return new Response(true, static::get($this->httpRequest));
    }

    /**
     * Get the data.
     * @param HttpRequest $request
     *
     * @return array
     */
    public static function get(HttpRequest $request)
    {
        // CSRF token
        $token = csrf_token();

        if ($token == null) {
            \Session::regenerateToken();
            $token = csrf_token();
        }

        // application name
        $name = config('app.name');

        // locale
        $locale            = config('app.locale');
        $fallback_locale   = config('app.fallback_locale');
        $available_locales = Arr::wrap(config('app.available_locales'));

        // authentication

        $_user = \Auth::user();
        $user  = $_user ? new User($_user) : null;

        // is admin
        $is_admin = $_user && $_user->is_admin ? true : false;

        // flash messages

        $flashMessages = new FlashMessages();
        $flashMessages->clearSession();

        $flash = $flashMessages->toPassable();

        // websocket host
        $socket_host = env('WEBSOCKET_PROTOCOL', 'http').
            '://'.
            env('WEBSOCKET_HOST', $request->getHost()).
            ':'.
            env('WEBSOCKET_PORT', 6001);

        return compact(
            'name',
            'token',
            'locale',
            'fallback_locale',
            'available_locales',
            'user',
            'is_admin',
            'flash',
            'socket_host'
        );
    }
}
