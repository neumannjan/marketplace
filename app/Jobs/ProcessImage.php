<?php

namespace App\Jobs;

use App\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Image as InterventionImage;

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
     * @param InterventionImage $interventionImage
     * @return void
     */
    public function handle(InterventionImage $interventionImage)
    {
        if (!$this->image->original)
            throw new \InvalidArgumentException("Image does not have a file");

        if (!file_exists($this->image->original))
            throw new \InvalidArgumentException("The Image's file does not exist");

        $sizes = $this->image->available_sizes;

        if (!is_array($sizes) && !$sizes) {
            $sizes = array_keys(Image::SIZES);
        }

        foreach ($sizes as $size) {
            $size = Image::SIZES[$size];

            // create image
            $iImg = $interventionImage->make($this->image->original);

            if (!is_array($size))
                $size = [$size, $size];

            //TODO
        }
    }
}
