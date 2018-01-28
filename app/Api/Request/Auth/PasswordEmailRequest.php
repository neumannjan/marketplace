<?php

namespace App\Api\Request\Auth;


use App\Api\Request\Request;
use App\Api\Response\Response;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

/**
 * TODO documentation
 */
class PasswordEmailRequest extends Request
{

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
    protected function rules(Validator $validator = null)
    {
        return [
            'email' => 'required|email'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $response = \Password::broker()->sendResetLink([
            'email' => $parameters['email']
        ]);

        switch ($response) {
            case PasswordBroker::RESET_LINK_SENT:
                \Session::flash("success.password-reset-link-sent", trans($response));
                return new Response(true, []);
            default:
                throw ValidationException::withMessages([
                    'email' => [trans($response)]
                ]);
        }
    }
}