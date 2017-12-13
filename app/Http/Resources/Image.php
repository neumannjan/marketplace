<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Image extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $array = [
            'id' => $this->id,
            'width' => $this->width,
            'height' => $this->height
        ];

        foreach (\App\Image::SIZES as $size) {
            $size = "size_$size";
            $array[$size] = $this->$size;
        }

        return $array;
    }
}
