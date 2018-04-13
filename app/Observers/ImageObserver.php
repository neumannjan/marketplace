<?php

namespace App\Observers;

use App\Image;
use App\Jobs\DeleteImage;


/**
 * Event observer for the Image model
 */
class ImageObserver
{
    /**
     * @param Image $image
     *
     * @throws \Exception
     */
    function updated(Image $image)
    {
        // if there are no links pointing to this image, delete
        if ($image->offer == null && $image->user == null) {
            $image->delete();
        }
    }

    /**
     * @param Image $image
     */
    function updating(Image $image)
    {
        if ($image->isDirty(['offer_id']) && $image->offer) {
            // `offer_id` value was modified, the images have therefore changed and may be inappropriate
            $image->offer->resetAppropriateness();
        }
    }

    /**
     * @param Image $image
     *
     * @return bool
     */
    function deleting(Image $image)
    {
        // delete image files
        DeleteImage::dispatch($image);

        return true;
    }

    /**
     * @param Image $image
     */
    function created(Image $image)
    {
        if ($image->offer) {
            // There were new images added to an offer and they may be inappropriate
            $image->offer->resetAppropriateness();
        }
    }
}