<?php

namespace App\Api\Response;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

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
     * @return string
     */
    final function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAsArray()
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
     * @param int $options
     * @return string
     */
    public function getAsJson($options = 0)
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