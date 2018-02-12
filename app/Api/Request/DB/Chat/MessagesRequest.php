<?php

namespace App\Api\Request\DB\Chat;


use App\Api\Request\DB\MultiRequest;
use App\Http\Resources\AnyMessage;
use App\Http\Resources\OwnedMessage;
use App\Message;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;

class MessagesRequest extends MultiRequest
{
    protected $modelClass = Message::class;

    protected $defaultScope = Message::SCOPE_PERSONAL;

    protected $orderBased = true;

    /**
     * @var Guard
     */
    protected $guard;

    /**
     * MessagesRequest constructor.
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        parent::__construct($this->modelClass, $this->resourceClass, $this->orderBased);
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
    protected function resourceClass(Collection $parameters)
    {
        switch ($parameters->get('scope', $this->defaultScope)) {
            case Message::SCOPE_ANY:
                return AnyMessage::class;
            case Message::SCOPE_PERSONAL:
            default:
                return OwnedMessage::class;
        }
    }
}