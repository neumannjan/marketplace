<?php

namespace App\Api\Request\DB\Offer;


use App\Image;
use App\Jobs\ProcessImage;
use App\Offer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * Classes can make use of this trait to be able to process images based on
 * their requested order.
 *
 * @package App\Api\Request\DB\Offer
 */
trait ProcessImages
{
    /**
     * Remove old images, save new and reorder them.
     *
     * @param Offer $offer
     * @param array $imageOrder
     * @param UploadedFile|UploadedFile[] $newImages
     * @param boolean $handleExisting
     *
     * @throws \Exception
     */
    protected function processImages(
        $offer,
        $imageOrder,
        $newImages,
        $handleExisting
    ) {
        if ($newImages && (array)$newImages !== $newImages) {
            $newImages = [$newImages];
        }

        $newImages = Collection::make($newImages ? $newImages : []);

        if ($handleExisting) {
            // fetch all existing images
            $existingImages = Image::query()->where(['offer_id' => $offer->id])
                ->get();
        }

        /** @var Collection $imageDesc */
        foreach ($imageOrder as $key => $imageDesc) {
            if ($imageDesc['new']) {
                $uploadedFile = $newImages->get($imageDesc['id'], null);

                if ( ! $uploadedFile) {
                    continue;
                }

                /** @var UploadedFile $uploadedFile */
                $originalFile
                    = $uploadedFile->storePublicly(Image::STORAGE_DIR);

                $image = new Image([
                    'original' => $originalFile,
                    'offer_id' => $offer->id,
                    'available_sizes' => ['tiny'],
                    'order' => $key,
                ]);

                $image->save();

                ProcessImage::dispatch($image);
            } elseif ($handleExisting) {
                // update the existing image and remove it from the array

                $k = $existingImages->search(function ($image) use ($imageDesc
                ) {
                    return ((int)$image['id']) === ((int)$imageDesc['id']);
                });

                if ($k !== false) {
                    $image = $existingImages->pull($k);
                    /** @var Image $image */
                    $image->order = $key;
                    $image->save();
                }
            }
        }

        //handle existing

        if ($handleExisting) {
            // remove all existing images that are still present in the array

            foreach ($existingImages as $image) {
                /** @var Image $image */
                $image->delete();
            }
        }
    }
}