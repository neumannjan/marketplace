<?php

namespace App\Http\Controllers;


use App\Api\Request\Auth\LoginRequest;
use App\Api\Request\Auth\LogoutRequest;
use App\Api\Request\Auth\PasswordEmailRequest;
use App\Api\Request\Auth\PasswordResetRequest;
use App\Api\Request\Auth\RegisterRequest;
use App\Api\Request\DB\Chat\ConversationsRequest;
use App\Api\Request\DB\Chat\MessageReceivedNotifyRequest;
use App\Api\Request\DB\Chat\MessageSendRequest;
use App\Api\Request\DB\Chat\MessagesRequest;
use App\Api\Request\DB\MultiRequest;
use App\Api\Request\DB\Offer\OfferBumpRequest;
use App\Api\Request\DB\Offer\OfferCreateRequest;
use App\Api\Request\DB\Offer\OfferEditRequest;
use App\Api\Request\DB\Offer\OfferMarkAppropriateRequest;
use App\Api\Request\DB\Offer\OfferRemoveRequest;
use App\Api\Request\DB\Offer\OfferReportRequest;
use App\Api\Request\DB\Offer\OfferSearchRequest;
use App\Api\Request\DB\SingleRequest;
use App\Api\Request\DB\User\UserAdminRequest;
use App\Api\Request\DB\User\UserSearchRequest;
use App\Api\Request\DB\User\UserSettingsRequest;
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
    /**
     * Associative array of request classes and their endpoint names
     *
     * @var array
     */
    private $requests;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->requests = [
            // Global
            'global' => GlobalDataRequest::class,

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
                'orderBased' => true,
            ],

            'offer' => [
                SingleRequest::class,
                'modelClass' => Offer::class,
                'resourceClass' => \App\Http\Resources\Offer::class,
            ],

            'users' => [
                MultiRequest::class,
                'modelClass' => User::class,
                'resourceClass' => \App\Http\Resources\User::class,
            ],

            'user' => [
                SingleRequest::class,
                'modelClass' => User::class,
                'resourceClass' => \App\Http\Resources\User::class,
            ],

            'search' => OfferSearchRequest::class,

            'conversations' => ConversationsRequest::class,
            'messages' => MessagesRequest::class,
            'message-send' => MessageSendRequest::class,
            'message-received-notify' => MessageReceivedNotifyRequest::class,

            // DB set
            'offer-create' => OfferCreateRequest::class,
            'offer-remove' => OfferRemoveRequest::class,
            'offer-edit' => OfferEditRequest::class,
            'offer-bump' => OfferBumpRequest::class,
            'offer-report' => OfferReportRequest::class,
            'offer-mark-appropriate' => OfferMarkAppropriateRequest::class,
            'user-settings' => UserSettingsRequest::class,

            // Admin
            'user-admin' => UserAdminRequest::class,
            'user-search' => UserSearchRequest::class,
        ];
    }

    /**
     * @param array       $data
     * @param Request     $request
     * @param Application $app
     *
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
                        $class      = array_shift($requestDefinition);
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

                    $parameters  = $parameters + $request->allFiles();
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

    /**
     * Controller method for the index route
     * @param Request     $request
     * @param Application $app
     *
     * @return JsonResponse
     */
    public function index(Request $request, Application $app)
    {
        return $this->resolve(json_decode($request->input("api"), true),
            $request, $app);
    }

    /**
     * Controller method for a single request route
     * @param             $name
     * @param Request     $request
     * @param Application $app
     *
     * @return JsonResponse
     */
    public function single($name, Request $request, Application $app)
    {
        return $this->resolve([
            $name => $request->input(),
            'global' => ''
            //add global request automatically for single requests
        ], $request, $app);
    }
}