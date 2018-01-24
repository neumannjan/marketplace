<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validation rule that asserts that the value consists
 * only of letters, numbers, dashes and underscores.
 */
class SlugRule implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = preg_match('/^[a-zA-Z0-9-_]+$/', $value);

        if ($result === false) {
            throw new \RuntimeException();
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
