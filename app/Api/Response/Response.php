<?php

namespace App\Api\Response;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * API Response class.
 *
 * @see \App\Api\Response\ResponseInterface
 */
class Response implements ResponseInterface
{
    /** @var string */
    private $name;

    /** @var bool */
    protected $success;

    /** @var mixed */
    protected $content;

    /**
     * @param bool  $success
     * @param mixed $content
     *
     * @internal param string $name
     */
    public function __construct($success, $content)
    {
        $this->success = $success;
        $this->content = $content;
    }

    /**
     * @param string $name
     */
    final function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    final function getName()
    {
        return $this->name;
    }

    /**
     * Ensure that a response name has been set
     */
    private function assertHasName()
    {
        if ($this->name == null || ! is_string($this->name)) {
            throw new \RuntimeException("Response does not have a name");
        }
    }

    /**
     * Get response content
     *
     * @return mixed
     */
    protected function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $this->assertHasName();

        $raw = $this->getContent();

        if ($raw instanceof Arrayable) {
            $result = $raw->toArray();
        } else {
            $result = (array)$raw;
        }

        return [
            $this->name => [
                "success" => (bool)$this->success,
                "result" => $result,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function toJson($options = 0)
    {
        $this->assertHasName();

        $raw = $this->getContent();

        if ($raw instanceof Jsonable) {
            $result = $raw->toJson($options);
        } else {
            $result = json_encode($raw, $options);
        }

        $success = $this->success ? "true" : "false";
        $result  = "{\"success\":$success,\"result\":$result}";

        return "{\"$this->name\":$result}";
    }
}