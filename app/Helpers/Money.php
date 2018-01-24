<?php

namespace App\Helpers;


use Money\Currencies\AggregateCurrencies;
use Money\Currencies\BitcoinCurrencies;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\AggregateMoneyFormatter;
use Money\Formatter\BitcoinMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Parser\DecimalMoneyParser;

/**
 * A single class to provide both a money parser and a money formatter
 */
class Money
{
    /** @var \Money\MoneyParser */
    protected $decimalParser;
    /** @var \Money\MoneyFormatter */
    protected $formatter;
    /** @var AggregateCurrencies */
    protected $currencies;

    /**
     * Money constructor.
     * @param string $locale
     */
    public function __construct($locale)
    {
        $bitcoinCurrencies = new BitcoinCurrencies();
        $isoCurrencies = new ISOCurrencies();
        $this->currencies = $allCurrencies = new AggregateCurrencies([
            $bitcoinCurrencies,
            $isoCurrencies
        ]);

        $numberFormatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        $moneyFormatter = new AggregateMoneyFormatter([
            'XBT' => new BitcoinMoneyFormatter(4, $bitcoinCurrencies),
            '*' => new IntlMoneyFormatter($numberFormatter, $isoCurrencies)
        ]);

        $moneyParser = new DecimalMoneyParser($allCurrencies);

        $this->decimalParser = $moneyParser;
        $this->formatter = $moneyFormatter;
    }

    /**
     * @return \Money\MoneyParser
     */
    public function getDecimalParser()
    {
        return $this->decimalParser;
    }

    /**
     * @return \Money\MoneyFormatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @return \Money\Currencies\AggregateCurrencies
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }
}