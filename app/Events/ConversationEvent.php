<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;

trait ConversationEvent
{
    protected function getConversationChannel($username1, $username2)
    {
        if (strcmp($username1, $username2) <= 0) {
            return new PrivateChannel("conversation.$username1.$username2");
        } else {
            return new PrivateChannel("conversation.$username2.$username1");
        }
    }
}