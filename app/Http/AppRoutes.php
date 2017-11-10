<?php

namespace App\Http;

/**
 * Class containing helper functions that generate frontend app routes.
 */
class AppRoutes
{

    /**
     * Private constructor, as this class contains static methods only
     */
    private function __construct()
    {
    }

    /**
     * URL to a frontend app route
     * @param string $route
     * @param bool $absolute
     * @return string
     */
    public static final function route($route, $absolute = true)
    {
        return route('api', ['route' => $route], true);
    }

    /**
     * URL to frontend app index route
     * @param bool $absolute
     * @return string
     */
    public static final function index($absolute = true)
    {
        return self::route('', $absolute);
    }

    /**
     * URL to frontend app password reset route
     * @param string $token password reset token
     * @param bool $absolute
     * @return string
     */
    public static final function passwordResetRoute($token, $absolute = true)
    {
        return self::route("token/$token", $absolute);
    }
}