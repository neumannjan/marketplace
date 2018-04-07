<?php

namespace App\Http\Controllers\Auth;


use App\User;
use Illuminate\Routing\Controller;

class ActivateController extends Controller
{
    /**
     * ActivateController constructor.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

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

    protected function redirectToLogin()
    {
        return redirect()
            ->route('app', ['route' => 'login']);
    }

    protected function redirectInvalidLink()
    {
        return $this->redirectToLogin()
            ->with('danger.activate-link-invalid',
                __('flash.danger.activate-link-invalid'));
    }

    protected function redirectSuccess()
    {
        return $this->redirectToLogin()
            ->with('success.activate', __('flash.success.activate'));
    }
}