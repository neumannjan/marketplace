<?php

namespace App\Observers;


use App\Offer;

/**
 * Event observer for the Offer model
 *
 * @package App\Observers
 */
class OfferObserver
{
    /**
     * @param Offer $offer
     *
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

    /**
     * @param Offer $offer
     */
    function updating(Offer $offer)
    {
        if ($offer->isDirty(['name', 'description'])) {
            $offer->resetAppropriateness();
        }
    }
}