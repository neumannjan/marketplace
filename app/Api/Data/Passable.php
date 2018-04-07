<?php

namespace App\Api\Data;

/**
 * Can be serialized and returned in an API response.
 */
interface Passable
{
    /**
     * Returns a serializable representation of the object.
     * (see the return types of this function)
     *
     * @return array|\stdClass|\JsonSerializable
     */
    function toPassable();
}