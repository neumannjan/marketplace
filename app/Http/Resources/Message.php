<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Message extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Message|Message $this */
        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'content' => $this->content,
            'additional' => MessageAdditional::make($this->resource),
            'from' => User::make($this->from),
            'to' => User::make($this->to),
            'received' => $this->received,
            'read' => $this->read
        ];
    }
}
