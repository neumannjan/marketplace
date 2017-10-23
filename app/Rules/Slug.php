<?php

namespace App\Rules;

use http\Exception\RuntimeException;
use Illuminate\Contracts\Validation\Rule;

class Slug implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = preg_match('/[a-zA-Z0-9-_]+/', $value);

        if ($result === false) {
            throw new RuntimeException();
        }

        return $result ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.slug');
    }
}
