<?php

namespace App\Observers;

use App\Image;
use App\Jobs\DeleteImage;


/**
 * Events for the {@see \App\Image Image} model
 */
class ImageObserver
{
    /**
     * @param Image $image
     * @throws \Exception
     */
    function updated(Image $image)
    {
        // if there are no links pointing to this image, delete
        if ($image->offer == null && $image->user == null) {
            $image->delete();
        }
    }

    function deleting(Image $image)
    {
        // delete image files
        DeleteImage::dispatch($image);
        return true;
    }
}