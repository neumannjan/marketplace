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
            throw new \InvalidArgumentException("The Image's file does not exist");
        }

        $availableSizes = $this->image->available_sizes;

        if (!is_array($availableSizes) && !$availableSizes) {
            $availableSizes = array_keys(Image::SIZES);
        }

        // create image
        $iImgOrig = $imageManager->make($origPath);

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

        //remove old file
        unlink($origPath);

        $this->image->width = $iImgOrig->getWidth();
        $this->image->height = $iImgOrig->getHeight();

        $sizes = [];

        foreach ($availableSizes as $sizeName) {
            $iImg = clone $iImgOrig;

            $size = Image::SIZES[$sizeName];

            if ($size[0] === null) {
                $size[0] = $size[1] * $this->image->width / $this->image->height;
            } elseif ($size[0] === null) {
                $size[1] = $size[0] * $this->image->height / $this->image->width;
            }

            $iImg->fit(round($size[0]), round($size[1]), function ($constraint) {
                /** @var Constraint $constraint */

                //prevent image from being upsized
                $constraint->upsize();
            });

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
