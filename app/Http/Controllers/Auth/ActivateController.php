<?php

namespace App\Http\Controllers\Auth;


use App\User;
use Illuminate\Routing\Controller;

/**
 * Controller for user activation
 *
 * @package App\Http\Controllers\Auth
 */
class ActivateController extends Controller
{
    /**
     * ActivateController constructor.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Activate a user
     *
     * @param string $username
     * @param string $token
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($username, $token)
    {
        $user = User::where('username', $username)->first();

        if ($user == null
            || $user->activate($token) === false
        ) { //activation done here
            return $this->redirectInvalidLink();
        }

        return $this->redirectSuccess();
    }

    /**
     * Redirect to the login page
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToLogin()
    {
        return redirect()
            ->route('app', ['route' => 'login']);
    }

    /**
     * Redirect an invalid activation link
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectInvalidLink()
    {
        return $this->redirectToLogin()
            ->with('danger.activate-link-invalid',
                __('flash.danger.activate-link-invalid'));
    }

    /**
     * Redirect a successful activation
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectSuccess()
    {
        return $this->redirectToLogin()
            ->with('success.activate', __('flash.success.activate'));
    }
}