<?php

namespace App\Api;


use App\Api\Request\Request as ApiRequest;
use App\Api\Response\Response as ApiResponse;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * TODO add documentation
 * TODO make request handle ValidationException and revert to it where possible (sendLockoutResponse will go away completely)
 */
class LoginRequest extends ApiRequest
{
    use AuthenticatesUsers;

    protected $login, $password, $remember;

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        return \Auth::guest();
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, $parameters)
    {
        $login = $password = $remember = null;
        extract($parameters, EXTR_IF_EXISTS);

        $this->login = $login;
        $this->password = $password;
        $this->remember = $remember;

        $request = \App::get('request');

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return new ApiResponse($name, false, $this->sendLockoutResponse($request));
        }

        if ($this->attemptLogin($request)) {
            return new ApiResponse($name, true, $this->sendLoginResponse($request));
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return new ApiResponse($name, false, $this->sendFailedLoginResponse($request));
    }

    protected function rules()
    {
        return [
            $this->username() => 'required|string',
            'password' => 'required|string',
            'remember' => 'required|boolean',
        ];
    }

    /**
     * @inheritDoc
     */
    public function username()
    {
        return 'login';
    }

    protected function getLoginKey($value)
    {
        return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? 'email' : 'username');
    }

    /**
     * @inheritDoc
     */
    protected function credentials(Request $request)
    {
        $loginKey = ($this->getLoginKey($this->login));

        return [
            $loginKey => $this->login,
            'password' => $this->password
        ];
    }

    /**
     * @inheritdoc
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if (($user = User::where($this->getLoginKey($this->login), $this->login)->first()) != null) {
            switch ($user->status) {
                case User::STATUS_INACTIVE:
                    $message = trans('auth.inactive');
                    break;
                case User::STATUS_BANNED:
                    $message = trans('auth.banned');
                    break;
                case User::STATUS_ACTIVE:
                default:
                    $message = trans('auth.failed');
                    break;

            }
        } else {
            $message = trans('auth.failed');
        }

        return [
            'validation' => [
                $this->username() => [$message]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return [
            'validation' => [
                $this->username() => [\Lang::get('auth.throttle', ['seconds' => $seconds])]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt($this->credentials($request), $this->remember);
    }

    /**
     * @inheritDoc
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        return [];
    }

    /**
     * @inheritDoc
     */
    protected function throttleKey(Request $request)
    {
        return Str::lower($this->login) . '|' . $request->ip();
    }

}