<?php

namespace App\Api\Request\Auth;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;

/**
 * TODO documentation
 */
class RegisterRequest extends Request
{
    /**
     * @inheritdoc
     */
    protected function shouldResolve()
    {
        return \Auth::guest();
    }

    /**
     * @inheritdoc
     */
    protected function rules()
    {
        $rules = User::getValidationRules();

        $rules['password'][] = 'confirmed';

        return $rules;
    }

    /**
     * @inheritdoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $user = User::create([
            'username' => $parameters['username'],
            'email' => $parameters['email'],
            'password' => \Hash::make($parameters['password']),
            'display_name' => $parameters['display_name'],
            'status' => User::STATUS_INACTIVE
        ]);

        event(new Registered($user));

        // do not login

        // send e-mail
        //TODO send only if set
        $user->sendRegistrationActivateNotification();

        // display a flash message
        \Session::flash('success.register', __('flash.success.register', [
            'email' => $user->email
        ]));

        return new Response(true, []);
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return \Auth::guard();
    }
}