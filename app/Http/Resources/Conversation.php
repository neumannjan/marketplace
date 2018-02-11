<?php

namespace App\Http\Resources;

use App\Message;
use Illuminate\Http\Resources\Json\Resource;

class Conversation extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Message|Conversation $this */
        return [
            'content' => $this->content,
            'additional' => $this->additional,
            'user' => $this->user,
        ];
    }
}
