<?php

namespace App\Http\Middleware;

use Closure;

class DevOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
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
