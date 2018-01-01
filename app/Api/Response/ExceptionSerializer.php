<?php

namespace App\Api\Response;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Tool to serialize exceptions
 */
class ExceptionSerializer implements \JsonSerializable, Arrayable, Jsonable
{

    /**
     * @var \Exception
     */
    protected $exception;

    /**
     * @var $verbose
     */
    protected $verbose;

    /**
     * ExceptionSerializer constructor.
     * @param \Exception $exception
     * @param bool|null $verbose Whether to display exception details. Determined by environment if not specified.
     */
    public function __construct(\Exception $exception, $verbose = null)
    {
        $this->exception = $exception;

        if ($verbose === null) {
            $verbose = (config('app.env') === 'local');
        }

        $this->verbose = $verbose;
    }

    protected function _toArray(\Exception $exception)
    {
        if ($this->verbose) {
            $previous = $exception->getPrevious();

            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
                'previous' => $previous ? $this->_toArray($previous) : null,
            ];
        } else {
            return true;
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->_toArray($this->exception);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function toJson($options = 0)
    {
        return json_encode($this, $options);
    }
}