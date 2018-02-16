<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class MessageAdditional extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $additional = $this->resource;

        if (isset($additional['offer'])) {
            $additional['offer'] = Offer::make(\App\Offer::find($additional['offer']));
        }

        return $additional;
    }
}
