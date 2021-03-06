<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;

/**
 * ConversationEvent trait that provides helper functions.
 *
 * @package App\Events
 */
trait ConversationEvent
{
    /**
     * Get the name of a conversation channel between two users.
     *
     * @param string $username1
     * @param string $username2
     *
     * @return PrivateChannel
     */
    protected function getConversationChannel($username1, $username2)
    {
        if (strcmp($username1, $username2) <= 0) {
            return new PrivateChannel("conversation.$username1.$username2");
        } else {
            return new PrivateChannel("conversation.$username2.$username1");
        }
    }
}