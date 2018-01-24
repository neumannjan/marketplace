<?php

namespace App\Rules;


use Illuminate\Contracts\Validation\Rule;
use Money\Parser\DecimalMoneyParser;


/**
 * Validation rule that asserts that the value is a correct price (without currency)
 */
class MoneyRule implements Rule
{

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $result = preg_match(DecimalMoneyParser::DECIMAL_PATTERN, $value);

        if ($result === false) {
            throw new \RuntimeException();
        }

        return $result ? true : false;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return __('validation.numeric');
    }
}