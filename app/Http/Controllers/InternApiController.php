<?php

namespace App\Http\Controllers;


use App\Api\GlobalRequest;
use App\Api\LoginRequest;
use App\Api\LogoutRequest;
use App\Api\RegisterRequest;
use App\Api\Request\Request as ApiRequest;
use App\Api\Response\CompositeResponse as CompositeApiResponse;
use App\Api\Response\Response as ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for the internal API that is used exclusively by the frontend.
 */
class InternApiController extends Controller
{
    private $requests;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->requests = [
            'global' => GlobalRequest::class,
            'login' => LoginRequest::class,
            'logout' => LogoutRequest::class,
            'register' => RegisterRequest::class,
        ];
    }

    public function index(Request $request)
    {
        $data = json_decode($request->input("api"), true);

        $responses = [];
        if ($data != null) {
            foreach ($data as $requestName => $parameters) {

                if (isset($this->requests[$requestName])) {
                    $class = $this->requests[$requestName];

                    /** @var ApiRequest $request */
                    $request = new $class();

                    if ($parameters instanceof \stdClass) {
                        $parameters = (array)$parameters;
                    } elseif ($parameters !== (array)$parameters) {
                        $parameters = [];
                    }

                    $responses[] = $request->resolve($requestName, $parameters);
                } else {
                    $responses[] = new ApiResponse($requestName, false, "Unknown request.");
                }
            }
        }

        $compositeResponse = new CompositeApiResponse($responses);

        return new JsonResponse($compositeResponse);
    }
}