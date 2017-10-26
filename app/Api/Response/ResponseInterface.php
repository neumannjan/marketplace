<?php

namespace App\Api\Response;


/**
 * API response interface. Its getAs.. functions produce the final result to be returned by the API.
 */
interface ResponseInterface
{
    /**
     * Name of this response
     * @return string
     */
    function getName();

    /**
     * Return the data as an array
     * @return array
     */
    function getAsArray();

    /**
     * Return the data as a JSON string
     * @param int $options {@see json_encode} options
     * @return string
     */
    function getAsJson($options = 0);
}