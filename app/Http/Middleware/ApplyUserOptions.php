<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Foundation\Application;

/**
 * Applies options configured by the user
 */
class ApplyUserOptions
{
    /** @var Application */
    protected $application;

    /**
     * ApplyUserOptions constructor.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var User | null $user */
        $user = $request->user();

        if ($user !== null) {
            $this->application->setLocale($user->locale);
        }

        return $next($request);
    }
}
