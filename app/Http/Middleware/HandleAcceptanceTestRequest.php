<?php

namespace App\Http\Middleware;

use App\Tests\TestHelper;
use Closure;
use Illuminate\Http\Response;

/**
 * Authenticates a test user if the source of the request is an acceptance test
 */
class HandleAcceptanceTestRequest
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
        $testHelper = \App::make(TestHelper::class);

        //is this a Selenium request ?
        if ($testHelper->isSeleniumRequest()) {

            // authenticate user
            $userId = $testHelper->authAs();

            if ($userId !== null) {
                \Auth::loginUsingId($userId);
            }

            // get the response
            /** @var Response $response */
            $response = $next($request);

            // set confirmation cookie
            $response->withCookie(cookie(TestHelper::RESPONSE_COOKIE_NAME));

            return $response;
        }

        // not a Selenium request
        return $next($request);
    }
}
