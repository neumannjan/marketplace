<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Offer extends Resource
{
    use LoadsAttributesByAuthorization;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Offer | \App\Offer $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'author' => User::make($this->author),
            'listed_at' => $this->listed_at->toIso8601String(),
            'price' => $this->when($this->price, $this->price),
            'price_value' => $this->price_value,
            'currency' => $this->currency_code,
            'description' => $this->description,
            'status' => $this->status,
            'images' => Image::collection($this->images),
            'bumps_left' => $this->whenOwned($this->bumps_left),
            'just_bumped' => $this->whenOwned($this->just_bumped),
            'expired' => $this->whenOwned($this->expired),
            'reported_times' => $this->whenAdmin($this->reported_times),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getOwnedBy(\App\User $user)
    {
        /** @var Offer | \App\Offer $this */
        return $this->author_user_id === $user->id;
    }
}
