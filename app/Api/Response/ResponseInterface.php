<?php

namespace App\Api\Response;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;


/**
 * API response interface. Its getAs.. functions produce the final result to be returned by the API.
 */
interface ResponseInterface extends Jsonable, Arrayable
{
    /**
     * Name of this response
     *
     * @return string
     */
    function getName();

    /**
     * Return the data as an array
     *
     * @return array
     */
    function toArray();

    /**
     * Return the data as a JSON string
     *
     * @param int $options json_encode options
     *
     * @return string
     */
    function toJson($options = 0);
}