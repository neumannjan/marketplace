<?php

namespace App\Api\Request\DB\User;


use App\Api\Request\Request;
use App\Api\Response\Response;
use App\Image;
use App\Jobs\ProcessImage;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class UserSettingsRequest extends Request
{
    /** @var Guard */
    protected $guard;

    /**
     * UserSettingsRequest constructor.
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
        $rules = User::getValidationRules(true);

        $rules['password'][] = 'confirmed';
        $rules['image'] = ['sometimes', 'file', 'image'];
        $rules['remove_image'] = ['sometimes', 'boolean'];

        return $rules;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    protected function doResolve($name, Collection $parameters)
    {
        /** @var User $user */
        $user = $this->guard->user();
        $user->email = $parameters['email'];
        $user->display_name = $parameters->get('display_name');

        $passwordChanged = null;
        if ($parameters->has('password')) {
            $user->password = \Hash::make($parameters['password']);
            $passwordChanged = true;
        }

        $imageChanged = null;
        if ($parameters->has('image') || $parameters->get('remove_image')) {
            if ($user->profile_image !== null) {
                $imageChanged = $user->profile_image->delete() ? true : false;
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $parameters->get('image');
            if ($uploadedFile) {
                $originalFile = $uploadedFile->storePublicly(Image::STORAGE_DIR);

                $image = new Image([
                    'original' => $originalFile,
                    'available_sizes' => ['icon', 'icon_2x'],
                    'order' => 0
                ]);

                $imageChanged = $image->save() ? true : false;

                ProcessImage::dispatch($image);

                $user->profile_image_id = $image->id;
            }
        }

        if ($user->save()) {
            $result = [
                'user' => new \App\Http\Resources\User($user),
            ];

            if ($passwordChanged !== null) {
                $result['password'] = $passwordChanged;
            }

            if ($imageChanged !== null) {
                $result['image'] = $imageChanged;
            }

            return new Response(true, $result);
        } else {
            return new Response(false, []);
        }
    }

}