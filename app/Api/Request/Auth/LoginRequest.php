<?php

namespace App\Api\Request\Auth;


use App\Api\Request\Request as ApiRequest;
use App\Api\Response\Response as ApiResponse;
use App\Auth\AuthenticatesUsers;
use Illuminate\Support\Collection;

/**
 * API login request
 */
class LoginRequest extends ApiRequest
{

    use AuthenticatesUsers;

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
    protected function doResolve($name, Collection $parameters)
    {
        $login = $password = $remember = null;
        extract($parameters->all(), EXTR_IF_EXISTS);

        $response = $this->login($login, $password, $remember);

        if (!$response instanceof ApiResponse) {
            return new ApiResponse(true, $response);
        }

        return $response;
    }

    protected function rules()
    {
        return [
            'login' => 'required|string',
            'password' => 'required|string',
            'remember' => 'required|boolean',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function sendLoginResponse()
    {
        return [];
    }

}