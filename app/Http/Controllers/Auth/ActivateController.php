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

        if ($user == null || $user->activate($token) === false) { //activation done here
            return $this->redirectInvalidLink();
        }

        return $this->redirectSuccess();
    }

    protected function redirectInvalidLink()
    {
        return redirect()
            ->route('login')
            ->with('danger.activate-link-invalid', __('flash.danger.activate-link-invalid'));
    }

    protected function redirectSuccess()
    {
        return redirect()
            ->route('login')
            ->with('success.activate', __('flash.success.activate'));
    }
}