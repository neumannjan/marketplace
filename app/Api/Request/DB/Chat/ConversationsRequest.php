<?php

namespace App\Api\Request\DB\Chat;


use App\Api\Request\PaginatedRequest;
use App\Eloquent\Order\AfterPaginator;
use App\Http\Resources\Conversation;
use App\Message;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class ConversationsRequest extends PaginatedRequest
{

    /** @var Guard */
    protected $guard;

    protected $orderBased = true;

    /**
     * ConversationsRequest constructor.
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        return $this->guard->check();
    }

    /**
     * @inheritDoc
     */
    protected function rules(Validator $validator = null)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function urlParameters(Collection $parameters)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function paginator(Collection $parameters, $perPage, $pageOrAfter)
    {
        /** @var User $user */
        $user = $this->guard->user();

        return AfterPaginator::fromQuery(Message::conversationsWith($user->id), $perPage, $pageOrAfter);
    }

    /**
     * @inheritDoc
     */
    protected function resourceClass(Collection $parameters)
    {
        return Conversation::class;
    }
}