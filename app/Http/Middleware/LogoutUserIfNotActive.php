<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

/**
 * Middleware that logs out users that are inactive or banned.
 */
class LogoutUserIfNotActive
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
        if (\Auth::check()) {
            /** @var User $user */
            $user = $request->user();

            if ($user->status !== User::STATUS_ACTIVE) {
                \Auth::logout();
                return \Response::redirectToRoute('index')
                    ->with('warning.session-expired', __('flash.warning.session-expired'));
            }
        }

        return $next($request);
    }
}
