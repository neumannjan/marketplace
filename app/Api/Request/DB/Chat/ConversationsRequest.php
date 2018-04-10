<?php

namespace App\Api\Request\DB\Chat;


use App\Api\Request\DB\MultiRequest;
use App\Http\Resources\Conversation;
use App\Message;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

/**
 * API Request for a list of conversations
 *
 * @package App\Api\Request\DB\Chat
 */
class ConversationsRequest extends MultiRequest
{
    protected $modelClass = Message::class;
    protected $resourceClass = Conversation::class;
    protected $orderBased = true;

    protected $defaultScope = Message::SCOPE_PERSONAL;

    /** @var Guard */
    protected $guard;

    /**
     * ConversationsRequest constructor.
     *
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        parent::__construct($this->modelClass, $this->resourceClass,
            $this->orderBased);
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     *
     * @param Validator|null $validator
     *
     * @return array
     */
    protected function rules(Validator $validator = null)
    {
        return [
            'unread' => 'sometimes|boolean',
        ];
    }

    /**
     * @inheritDoc
     *
     * @param Collection $parameters
     *
     * @return array
     */
    protected function urlParameters(Collection $parameters)
    {
        return ['unread'];
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
     * @param            $query
     * @param Collection $parameters
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
     */
    protected function additionalQuery($query, Collection $parameters)
    {
        /** @var User $user */
        $user = $this->guard->user();

        $query->scopes(['conversationsWith' => $user->username]);

        if ($parameters->get('unread', false)) {
            $query->where(['read' => false])
                ->where(['to_username' => $user->username]);
        }

        return $query;
    }
}