<?php

namespace App\Events;

use App\Message;
use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * MessageReceived event that can be broadcast to the frontend
 *
 * @package App\Events
 */
class MessageReceived implements ShouldBroadcast
{
    use ConversationEvent, Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Message */
    protected $message;

    /**
     * User that received the message
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     * @param User    $user User that received the message
     */
    public function __construct(Message $message, User $user)
    {
        $this->message = $message;
        $this->user    = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->getConversationChannel($this->message->from_username,
            $this->message->to_username);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'read' => $this->message->read,
            'username' => $this->user->username,
        ];
    }
}
