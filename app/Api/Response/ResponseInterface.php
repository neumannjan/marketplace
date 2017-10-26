<?php

namespace App\Api\Response;


interface ResponseInterface
{
    function getName();

    function getAsArray();

    function getAsJson($options = 0);
}