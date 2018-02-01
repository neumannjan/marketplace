<?php

namespace App\Observers;


use App\Offer;

class OfferObserver
{
    /**
     * @param Offer $offer
     * @return bool
     * @throws \Exception
     */
    function deleting(Offer $offer)
    {
        // delete images (to dispatch appropriate image delete events)
        foreach ($offer->images as $image) {
            $image->delete();
        }

        return true;
    }
}