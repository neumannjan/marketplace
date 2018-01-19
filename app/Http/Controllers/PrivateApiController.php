<?php

namespace App\Http\Controllers;


use App\Api\Request\Auth\LoginRequest;
use App\Api\Request\Auth\LogoutRequest;
use App\Api\Request\Auth\PasswordEmailRequest;
use App\Api\Request\Auth\PasswordResetRequest;
use App\Api\Request\Auth\RegisterRequest;
use App\Api\Request\DB\BasicMultiRequest;
use App\Api\Request\DB\BasicSingleRequest;
use App\Api\Request\GlobalRequest;
use App\Api\Request\Request as ApiRequest;
use App\Api\Response\CompositeResponse as CompositeApiResponse;
use App\Api\Response\Response as ApiResponse;
use App\Offer;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for the internal API that is used exclusively by the frontend.
 */
class PrivateApiController extends Controller
{
    private $requests;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->requests = [
            //Global
            'global' => GlobalRequest::class,

            //Auth
            'login' => LoginRequest::class,
            'logout' => LogoutRequest::class,
            'register' => RegisterRequest::class,
            'password-email' => PasswordEmailRequest::class,
            'password-reset' => PasswordResetRequest::class,

            //DB
            'offers' => [BasicMultiRequest::class, Offer::class, \App\Http\Resources\Offer::class],
            'offer' => [BasicSingleRequest::class, Offer::class, \App\Http\Resources\Offer::class],
            'user' => [BasicSingleRequest::class, User::class, \App\Http\Resources\User::class],
        ];
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    protected function resolve($data)
    {
        $responses = [];
        if ($data != null) {
            foreach ($data as $name => $parameters) {

                if (isset($this->requests[$name])) {
                    $requestDefinition = $this->requests[$name];

                    /** @var ApiRequest $apiRequest */
                    $apiRequest = null;

                    if (is_array($requestDefinition)) {
                        $class = array_shift($requestDefinition);
                        $apiRequest = new $class(...$requestDefinition);
                    } else {
                        $apiRequest = new $requestDefinition();
                    }

                    if ($parameters instanceof \stdClass) {
                        $parameters = (array)$parameters;
                    } elseif ($parameters !== (array)$parameters) {
                        $parameters = [];
                    }

                    $responses[] = $apiRequest->resolve($name, $parameters);
                } else {
                    $response = new ApiResponse(false, "Unknown request.");
                    $response->setName($name);

                    $responses[] = $response;
                }
            }
        }

        $compositeResponse = new CompositeApiResponse($responses);

        return new JsonResponse($compositeResponse);
    }

    public function index(Request $request)
    {
        return $this->resolve(json_decode($request->input("api"), true));
    }

    public function single($name, Request $request)
    {
        return $this->resolve([
            $name => $request->input(),
            'global' => '' //add global request automatically for single requests
        ]);
    }
}