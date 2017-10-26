<?php

namespace App\Api\Response;


class CompositeResponse implements ResponseInterface
{
    /** @var Response[] */
    protected $responses;

    /**
     * @param Response[] $responses
     */
    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    /**
     * @return string
     */
    public final function getName()
    {
        return null;
    }

    public function getAsArray()
    {
        $result = [];

        foreach ($this->responses as $response) {
            $r = $response->getAsArray();
            $result[$response->getName()] = array_first($r);
        }

        return $result;
    }

    public function getAsJson($options = 0)
    {
        $result = null;

        if ($this->responses != null) {
            $first = true;
            foreach ($this->responses as $response) {
                $r = substr($response->getAsJson($options), 1, -1);

                if ($first) {
                    $result = '{' . $r;
                } else {
                    $result .= ',' . $r;
                }

                $first = false;
            }

            return $result . '}';
        }

        return '{}';
    }
}