<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class OwnedMessage extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $user = \Auth::user();

        if (!$user) {
            return [];
        }

        /** @var \App\Message|OwnedMessage $this */
        return [
            'content' => $this->content,
            'additional' => $this->additional,
            'mine' => $this->from_id === $user->id
        ];
    }
}
