<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Offer extends Resource
{
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
            'price' => $this->price,
            'price_value' => $this->price_value,
            'currency' => $this->currency_code,
            'description' => $this->description,
            'status' => $this->status,
            'bumps_left' => $this->bumps_left,
            'just_bumped' => $this->just_bumped,
            'expired' => $this->expired,
            'images' => Image::collection($this->images),
        ];
    }
}
