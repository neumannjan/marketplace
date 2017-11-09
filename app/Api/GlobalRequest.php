<?php

namespace App\Api;


use App\Api\Request\Request;
use App\Api\Response\Response;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;

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
        /** @var Store $session */
        $session = \App::get('session');
        $flash = [
            'success' => (object) ($session->get('status', []) + $session->get('success', [])),
            'danger' => (object) ($session->get('danger', [])),
            'warning' => (object) ($session->get('warning', [])),
            'primary' => (object) ($session->get('primary', [])),
            'secondary' => (object) ($session->get('secondary', [])),
        ];

        return compact(
            'token',
            'is_authenticated',
            'flash'
            );
    }
}