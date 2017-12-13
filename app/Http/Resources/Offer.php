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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'images' => Image::collection($this->images),
        ];
    }
}
