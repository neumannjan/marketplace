<?php

namespace App\Http\Controllers;


use App\Api\Request\Auth\LoginRequest;
use App\Api\Request\Auth\LogoutRequest;
use App\Api\Request\Auth\PasswordEmailRequest;
use App\Api\Request\Auth\PasswordResetRequest;
use App\Api\Request\Auth\RegisterRequest;
use App\Api\Request\CachedDataRequest;
use App\Api\Request\DB\Chat\ConversationsRequest;
use App\Api\Request\DB\Chat\MessagesRequest;
use App\Api\Request\DB\MultiRequest;
use App\Api\Request\DB\Offer\OfferCreateRequest;
use App\Api\Request\DB\Offer\OfferSearchRequest;
use App\Api\Request\DB\SingleRequest;
use App\Api\Request\GlobalDataRequest;
use App\Api\Request\Request as ApiRequest;
use App\Api\Response\CompositeResponse as CompositeApiResponse;
use App\Api\Response\Response as ApiResponse;
use App\Offer;
use App\User;
use Illuminate\Foundation\Application;
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
            // Global
            'global' => GlobalDataRequest::class,
            'cached' => CachedDataRequest::class,

            // Auth
            'login' => LoginRequest::class,
            'logout' => LogoutRequest::class,
            'register' => RegisterRequest::class,
            'password-email' => PasswordEmailRequest::class,
            'password-reset' => PasswordResetRequest::class,

            // DB get
            'offers' => [
                MultiRequest::class,
                'modelClass' => Offer::class,
                'resourceClass' => \App\Http\Resources\Offer::class,
                'orderBased' => true
            ],

            'offer' => [
                SingleRequest::class,
                'modelClass' => Offer::class,
                'resourceClass' => \App\Http\Resources\Offer::class
            ],

            'user' => [
                SingleRequest::class,
                'modelClass' => User::class,
                'resourceClass' => \App\Http\Resources\User::class
            ],

            'search' => OfferSearchRequest::class,

            'conversations' => ConversationsRequest::class,
            'messages' => MessagesRequest::class,

            // DB set
            'offer-create' => OfferCreateRequest::class
        ];
    }

    /**
     * @param array $data
     * @param Request $request
     * @param Application $app
     * @return JsonResponse
     */
    protected function resolve($data, Request $request, Application $app)
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
                        $apiRequest = $app->make($class, $requestDefinition);
                    } else {
                        $apiRequest = $app->make($requestDefinition);
                    }

                    $apiRequest->setHttpRequest($request);

                    if ($parameters instanceof \stdClass) {
                        $parameters = (array)$parameters;
                    } elseif ($parameters !== (array)$parameters) {
                        $parameters = [];
                    }

                    $parameters = $parameters + $request->allFiles();
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

    public function index(Request $request, Application $app)
    {
        return $this->resolve(json_decode($request->input("api"), true), $request, $app);
    }

    public function single($name, Request $request, Application $app)
    {
        return $this->resolve([
            $name => $request->input(),
            'global' => '' //add global request automatically for single requests
        ], $request, $app);
    }
}