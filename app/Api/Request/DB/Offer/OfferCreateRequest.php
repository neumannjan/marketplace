<?php

namespace App\Api\Request\DB\Offer;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Image;
use App\Jobs\ProcessImage;
use App\Offer;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class OfferCreateRequest extends Request
{

    /** @var Guard */
    protected $guard;

    /**
     * OfferCreateRequest constructor.
     * @param Guard $guard
     */
    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @inheritDoc
     */
    protected function shouldResolve()
    {
        return $this->guard->check();
    }

    /**
     * @inheritDoc
     */
    protected function rules(Validator $validator = null)
    {
        return [
                'imageOrder' => 'required|array',
                'imageOrder.*.new' => 'required|boolean',
                'imageOrder.*.index' => 'required|integer',
            ] + Offer::getValidationRules($validator);
    }

    /**
     * @inheritDoc
     */
    protected function jsonParameters()
    {
        return ['imageOrder'];
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $offer = new Offer([
            'name' => $parameters['name'],
            'description' => $parameters->get('description'),
            'price_value' => $parameters->get('price', 0),
            'currency' => $parameters->get('currency'),
            'status' => $parameters->get('status', Offer::STATUS_AVAILABLE),
            'author_user_id' => $this->guard->id()
        ]);

        $offer->save();

        // TODO add images
        $images = $parameters['images'];

        if ((array)$images !== $images)
            $images = [$images];

        $images = Collection::make($images);

        $imageOrder = $parameters['imageOrder'];


        /** @var Collection $imageDesc */
        foreach ($imageOrder as $key => $imageDesc) {
            if ($imageDesc['new']) {
                $uploadedFile = $images->get($imageDesc['index'], null);

                if (!$uploadedFile)
                    continue;

                /** @var UploadedFile $uploadedFile */
                $originalFile = $uploadedFile->storePublicly(Image::STORAGE_DIR);

                $image = new Image([
                    'original' => $originalFile,
                    'offer_id' => $offer->id,
                    'available_sizes' => ['tiny'],
                    'order' => $key
                ]);

                $image->save();

                ProcessImage::dispatch($image);
            }
        }

        return new Response(true, ['id' => $offer->id]);
    }

}