<?php

namespace App\Api\Request;


use App\Api\Response\Response;
use Illuminate\Support\Collection;

class DummyRequest extends Request
{
    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        return new Response(true, []);
    }

}