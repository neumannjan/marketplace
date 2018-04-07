<?php

namespace App\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * A Laravel `Facade` for {@see \App\Helpers\DevPackages DevPackages}.
 */
class DevPackages extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return 'dev-packages';
    }

}