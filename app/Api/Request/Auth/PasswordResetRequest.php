<?php

namespace App\Api\Request\Auth;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

/**
 * TODO documentation
 */
class PasswordResetRequest extends Request
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
    protected function rules(Validator $validator = null)
    {
        $baseRules = User::getValidationRules();

        $passwordRules = $baseRules['password'];
        $passwordRules[] = 'confirmed';

        return [
            'token' => 'required',
            'password' => $passwordRules,
            'email' => 'required|email'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $response = \Password::broker()->reset([
            'email' => $parameters['email'],
            'password' => $parameters['password'],
            'password_confirmation' => $parameters['password_confirmation'],
            'token' => $parameters['token'],
        ], function (User $user, $password) {
            // do reset password

            $user->password = \Hash::make($password);

            $user->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        switch ($response) {
            case PasswordBroker::PASSWORD_RESET:
                \Session::flash("success.password-reset", trans($response));
                return new Response(true, []);
            default:
                throw ValidationException::withMessages([
                    'email' => [trans($response)]
                ]);
        }
    }
}