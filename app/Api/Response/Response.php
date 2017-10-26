<?php

namespace App\Api\Response;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * API Response class.
 * @see \App\Api\Response\ResponseInterface
 */
class Response implements ResponseInterface
{
    /** @var string */
    private $name;

    /** @var bool */
    protected $success;

    /** @var mixed */
    private $content;

    /**
     * @param string $name
     * @param bool $success
     * @param mixed $content
     */
    public function __construct($name, $success, $content)
    {
        $this->name = $name;
        $this->success = $success;
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    final function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $raw = $this->content;

        if ($raw instanceof Arrayable) {
            $result = $raw->toArray();
        } else {
            $result = (array)$raw;
        }

        return [
            $this->name => [
                "success" => (bool)$this->success,
                "result" => $result
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function toJson($options = 0)
    {
        $raw = $this->content;

        if ($raw instanceof Jsonable) {
            $result = $raw->toJson($options);
        } else {
            $result = json_encode($raw, $options);
        }

        $success = $this->success ? "true" : "false";
        $result = "{\"success\":$success,\"result\":$result}";
        return "{\"$this->name\":$result}";
    }
}