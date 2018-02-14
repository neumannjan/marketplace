<?php

namespace App\Events;

use App\Http\Resources\OwnedMessage;
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
     * Create a new event instance.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->identifier = $message->identifier;
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
            $this->getConversationChannel($this->message->from_username, $this->message->to_username)
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
        return \App\Http\Resources\Message::make($this->message)->toArray(null);
    }
}
