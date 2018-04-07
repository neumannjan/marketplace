<?php

namespace App\Events;

use App\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use ConversationEvent, Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Message */
    protected $message;

    /** @var string */
    protected $identifier;

    /**
     * Whether this message is the first in a while sent in the conversation by this user.
     *
     * @var boolean
     */
    protected $first;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     * @param boolean $first
     */
    public function __construct(Message $message, $first)
    {
        $this->message    = $message;
        $this->identifier = $message->identifier;
        $this->first      = $first;
    }

    /**
     * Get whether this message is the first in a while sent in the conversation by this user.
     *
     * @return bool
     */
    public function isFirst()
    {
        return $this->first;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel("user.{$this->message->to_username}"),
            $this->getConversationChannel($this->message->from_username,
                $this->message->to_username),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        if ($this->identifier) {
            $this->message->identifier = $this->identifier;
        }

        return \App\Http\Resources\Message::make($this->message)->resolve();
    }
}
