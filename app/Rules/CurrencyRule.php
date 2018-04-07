<?php

namespace App\Rules;


use App\Helpers\Money;
use Illuminate\Contracts\Validation\Rule;
use Money\Currency;


/**
 * Validation rule that asserts that the value is a correct currency code
 */
class CurrencyRule implements Rule
{

    /**
     * @var Money
     */
    protected $moneyHelper;

    /**
     * @param Money $moneyHelper
     */
    public function __construct(Money $moneyHelper)
    {
        $this->moneyHelper = $moneyHelper;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        return $this->moneyHelper->getCurrencies()
            ->contains(new Currency($value));
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return __('validation.default');
    }
}