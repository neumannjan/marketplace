<?php

namespace App\Auth;


use App\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Trait for user authentication. Inspired by the original
 * {@see \Illuminate\Foundation\Auth\AuthenticatesUsers AuthenticatesUsers} trait.
 */
trait AuthenticatesUsers
{
    /**
     * @var int Maximum amount of login attempts
     */
    protected $maxAttempts = 5;

    /**
     * @var int Time in minutes to block further login attempts for, once the limit is reached
     */
    protected $decayMinutes = 1;

    /**
     * @var string
     * Throttle key template. Variables `{login}` and `{ip}` will be replaced with appropriate data.
     */
    protected $throttleKey = '{login}|{ip}';

    /**
     * Try to log in and return an appropriate response
     * @param string $login Username or email
     * @param string $password
     * @param boolean $remember
     * @param Request|null $request
     * @throws ValidationException
     * @return mixed
     */
    protected function login($login, $password, $remember, Request $request = null)
    {
        if ($request == null) {
            $request = \App::get('request');
        }

        // check for the amount of attempts
        $throttleKey = $this->throttleKey;
        $throttleKey = str_replace('{login}', Str::lower($login), $throttleKey);
        $throttleKey = str_replace('{ip}', $request->ip(), $throttleKey);

        $limiter = $this->limiter();

        if ($limiter->tooManyAttempts($throttleKey, $this->maxAttempts)) {
            // disable access
            event(new Lockout($request));

            $seconds = $limiter->availableIn($throttleKey);
            return $this->sendLockoutResponse($seconds);
        }

        // attempt login
        $credentials = [
            UserProvider::LOGIN_KEY => $login,
            'password' => $password,
        ];

        $guard = $this->guard();
        if ($guard->attempt($credentials, $remember)) {
            // success

            $request->session()->regenerate();

            //clear login attempts
            $limiter->clear($throttleKey);

            return $this->sendLoginResponse();
        }

        // failure

        //increment the number of attempts to log in
        $limiter->hit($throttleKey, $this->decayMinutes);

        /** @var User $user */
        $user = $this->provider()->retrieveByCredentials($credentials);
        return $this->sendFailedLoginResponse($this->createFailedLoginMessage($user));
    }

    /**
     * Return an error message about why the login failed.
     * @param User|null $user
     * @return string
     */
    protected function createFailedLoginMessage($user)
    {
        if ($user != null) {
            switch ($user->status) {
                case User::STATUS_INACTIVE:
                    return trans('auth.inactive');
                case User::STATUS_BANNED:
                    return trans('auth.banned');
                case User::STATUS_ACTIVE:
                default:
                    return trans('auth.failed');

            }
        }

        return trans('auth.failed');
    }

    /**
     * Get the rate limiter instance
     * @return RateLimiter
     */
    protected function limiter()
    {
        return app(RateLimiter::class);
    }

    /**
     * Get the user provider instance
     * @return \Illuminate\Contracts\Auth\UserProvider
     */
    protected function provider()
    {
        return \Auth::getProvider();
    }

    /**
     * Get the guard to be used during authentication.
     * @return StatefulGuard
     */
    protected function guard()
    {
        return \Auth::guard();
    }

    /**
     * The return value of {@see login()} for when the user has been forbidden from attempting to log in.
     * @throws ValidationException
     * @param int $seconds Time in seconds for how long the user has to wait before they can log in again.
     * @return mixed
     */
    protected function sendLockoutResponse($seconds)
    {

        throw ValidationException::withMessages([
            UserProvider::LOGIN_KEY => [trans('auth.throttle', ['seconds' => $seconds])],
        ])->status(423);
    }

    /**
     * The return value of {@see login()} for when the login was successful.
     * @return mixed
     */
    protected abstract function sendLoginResponse();

    /**
     * The return value of {@see login()} for when the login was unsuccessful.
     * @throws ValidationException
     * @param string $failureMessage Message provided by {@see createFailedLoginMessage() }
     * @return mixed
     */
    protected function sendFailedLoginResponse($failureMessage)
    {

        throw ValidationException::withMessages([
            UserProvider::LOGIN_KEY => [$failureMessage],
        ]);
    }
}