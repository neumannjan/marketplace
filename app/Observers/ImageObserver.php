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

    function updating(Image $image)
    {
        if($image->isDirty(['offer_id']) && $image->offer) {
            // `offer_id` value was modified, the images have therefore changed and may be inappropriate
            $image->offer->resetAppropriateness();
        }
    }

    function deleting(Image $image)
    {
        // delete image files
        DeleteImage::dispatch($image);
        return true;
    }

    function created(Image $image)
    {
        if($image->offer) {
            // There were new images added to an offer and they may be inappropriate
            $image->offer->resetAppropriateness();
        }
    }
}