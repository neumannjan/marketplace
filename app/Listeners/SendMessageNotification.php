<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Notifications\MessageNotification;

/**
 * MessageSent event listener that sends a notification to the message recipient.
 *
 * @package App\Listeners
 */
class SendMessageNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MessageSent $event
     *
     * @return void
     */
    public function handle(MessageSent $event)
    {
        $message = $event->getMessage();

        if ($event->isFirst() || $message->offer_id !== null) {
            $message->to->notify(new MessageNotification($message));
        }
    }
}
