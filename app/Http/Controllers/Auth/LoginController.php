<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
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
        $loginValue = $request->get('login');
        $loginKey = ($this->getLoginKey($loginValue));

        return [
            $loginKey => $loginValue,
            'password' => $request->get('password')
        ];
    }

    /**
     * @inheritDoc
     */
    public function username()
    {
        return 'login';
    }

    /**
     * @inheritdoc
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $loginValue = $request->get('login');

        if (($user = User::where($this->getLoginKey($loginValue), $loginValue)->first()) != null) {
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

        throw ValidationException::withMessages([
            $this->username() => [$message],
        ]);
    }
}
