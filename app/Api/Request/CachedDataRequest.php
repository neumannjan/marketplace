<?php

namespace App\Api\Request;

use App\Api\Response\CachedResponse;
use App\Facades\Money;
use Illuminate\Cache\Repository;
use Illuminate\Support\Collection;
use Money\Currency;

/**
 * Request that contains variables that are unlikely to change and that the frontend might not request often.
 */
class CachedDataRequest extends Request
{
    /**
     * @var Repository
     */
    protected $cacheRepository;

    /**
     * @param Repository $cacheRepository
     */
    public function __construct(Repository $cacheRepository)
    {
        $this->cacheRepository = $cacheRepository;
    }

    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        return new CachedResponse(true, 'mainCachedData', function () {
            $array = [];

            //currencies
            $currencyContainer = Money::getCurrencies();
            $iterator = $currencyContainer->getIterator();

            $currencies = [];

            /** @var Currency $currency */
            foreach ($iterator as $currency) {
                $currencies[$currency->getCode()] = $currencyContainer->subunitFor($currency);
            }

            uksort($currencies, 'strnatcmp');

            $array['currencies'] = $currencies;

            return $array;
        }, $this->cacheRepository);
    }

}