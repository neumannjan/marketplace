<?php

namespace App\Api\Request\DB\Chat;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Events\MessageSent;
use App\Message;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class MessageSendRequest extends Request
{
    /** @var Guard */
    protected $guard;

    /**
     * MessageSendRequest constructor.
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
        return [
            'to' => 'required|string|min:1|max:2000',
            'content' => 'required|string',
            'additional' => 'sometimes|array',
            'identifier' => 'sometimes|string'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        /** @var User $user */
        $user = $this->guard->user();

        $message = new Message([
            'from_username' => $user->username,
            'to_username' => $parameters['to'],
            'content' => $parameters['content'],
            'additional' => $parameters->get('additional', null),
            'identifier' => $parameters->get('identifier', null)
        ]);

        $message->save();

        broadcast(new MessageSent($message))->toOthers();

        return new Response(true, \App\Http\Resources\Message::make($message));
    }

}