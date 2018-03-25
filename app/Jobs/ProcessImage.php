<?php

namespace App\Jobs;

use App\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;

/**
 * Queue job that processes uploaded images. It converts them to appropriate sizes.
 */
class ProcessImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Image */
    protected $image;

    /**
     * How many times this job should be attempted
     * @var int
     */
    public $tries = 1; //try only once

    /**
     * Create a new job instance.
     *
     * @param Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Execute the job.
     *
     * @param ImageManager $imageManager
     * @param Application $application
     * @return void
     */
    public function handle(ImageManager $imageManager, Application $application)
    {
        if (!$this->image->original) {
            throw new \InvalidArgumentException("Image does not have a file");
        }

        $laravelStoragePath = $application->storagePath() . DIRECTORY_SEPARATOR . 'app';
        $origPath = $laravelStoragePath . DIRECTORY_SEPARATOR . $this->image->original;

        if (!file_exists($origPath)) {
            throw new \InvalidArgumentException("The Image's file does not exist (not found at $origPath");
        }

        $availableSizes = $this->image->available_sizes;

        if (!is_array($availableSizes) && !$availableSizes) {
            $availableSizes = array_keys(Image::SIZES);
        }

        // create image
        $iImgOrig = $imageManager->make($origPath);

        $origWidth = $iImgOrig->getWidth();
        $origHeight = $iImgOrig->getHeight();

        // ensure original is jpg and not too large

        $path = $iImgOrig->dirname . DIRECTORY_SEPARATOR . $iImgOrig->filename . '.jpg';
        $iImgOrig->resize(Image::ORIGINAL_SIZE_LIMIT, Image::ORIGINAL_SIZE_LIMIT, function ($constraint) {
            /** @var Constraint $constraint */

            //keep aspect ratio
            $constraint->aspectRatio();

            //prevent image from being upsized
            $constraint->upsize();
        });
        $iImgOrig->save($path);

        $this->image->original = $this->getRelativePath($path, $laravelStoragePath);

        $this->image->width = $origWidth; //TODO set resized size ?
        $this->image->height = $origHeight;

        //remove old file
        unlink($origPath);

        $sizes = [];

        foreach ($availableSizes as $sizeName) {
            $iImg = clone $iImgOrig;

            $size = Image::SIZES[$sizeName];

            if (is_array($size)) {
                $iImg->fit($size[0], $size[1], function ($constraint) {
                    /** @var Constraint $constraint */

                    //prevent image from being upsized
                    $constraint->upsize();
                });
            } else {
                $iImg->resize($size, $size, function ($constraint) {
                    /** @var Constraint $constraint */

                    //keep aspect ratio
                    $constraint->aspectRatio();

                    //prevent image from being upsized
                    $constraint->upsize();
                });
            }

            $relativePath = Image::STORAGE_DIR . DIRECTORY_SEPARATOR . Str::random(40) . '.jpg';
            $path = $laravelStoragePath . DIRECTORY_SEPARATOR . $relativePath;

            $iImg->save($path);
            $sizes[$sizeName] = $relativePath;
        }

        $this->image->sizes = $sizes;
        $this->image->ready = true;

        $this->image->save();
    }

    protected function getRelativePath($path, $base)
    {
        $base = Str::finish($base, DIRECTORY_SEPARATOR);

        if (Str::startsWith($path, $base)) {
            return Str::replaceFirst($base, '', $path);
        }
        return $path;
    }
}
