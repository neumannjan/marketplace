<?php

namespace App\Api;


use App\Api\Request\Request as ApiRequest;
use App\Api\Response\Response as ApiResponse;
use App\Auth\AuthenticatesUsers;

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
    protected function doResolve($name, $parameters)
    {
        $login = $password = $remember = null;
        extract($parameters, EXTR_IF_EXISTS);

        $response = $this->login($login, $password, $remember);

        if (!$response instanceof ApiResponse) {
            return new ApiResponse($name, true, $response);
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