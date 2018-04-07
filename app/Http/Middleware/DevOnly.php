<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Middleware that allows access only if the app is in development.
 */
class DevOnly
{
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
        if (config('app.env') !== 'local') {
            abort(403);
        }

        return $next($request);
    }
}
