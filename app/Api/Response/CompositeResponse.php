<?php

namespace App\Api\Response;

/**
 * API Response that is able to combine multiple Responses into one.
 *
 * @see \App\Api\Response\ResponseInterface
 */
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

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->responses as $response) {
            $r                            = $response->toArray();
            $result[$response->getName()] = array_first($r);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function toJson($options = 0)
    {
        $result = null;

        if ($this->responses != null) {
            $first = true;
            foreach ($this->responses as $response) {
                $r = substr($response->toJson($options), 1, -1);

                if ($first) {
                    $result = '{'.$r;
                } else {
                    $result .= ','.$r;
                }

                $first = false;
            }

            return $result.'}';
        }

        return json_encode(new \stdClass());
    }
}