<?php

namespace App\Http\Controllers;


use App\Api\ByeRequest;
use App\Api\HelloRequest;
use App\Api\Request\Request as ApiRequest;
use App\Api\Response\CompositeResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InternApiController extends Controller
{
    private $requests;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->requests = [
            'hello' => HelloRequest::class,
            'bye' => ByeRequest::class
        ];
    }

    public function index(Request $request)
    {
        $data = json_decode($request->input("api"));

        $responses = [];
        if ($data != null) {
            foreach ($data as $requestName => $parameters) {

                $class = $this->requests[$requestName];

                /** @var ApiRequest $request */
                $request = new $class();

                if ($parameters instanceof \stdClass) {
                    $parameters = (array)$parameters;
                } elseif ($parameters !== (array)$parameters) {
                    $parameters = [];
                }

                $responses[] = $request->resolve($requestName, $parameters);
            }
        }

        $compositeResponse = new CompositeResponse($responses);

        return new JsonResponse($compositeResponse->getAsJson(), 200, [], true);
    }
}