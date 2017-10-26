<?php

namespace App\Api\Request;


use App\Api\Response\Response;
use Illuminate\Support\Facades\Validator;

/**
 * API request class
 */
abstract class Request
{

    /**
     * Decides whether the request will be resolved.
     * If the request should not run, but terminate with an error, returns the error message. Otherwise returns true.
     * @return true|string
     */
    protected function shouldResolve()
    {
        return true;
    }

    /**
     * Returns validation rules for the request parameters
     * @return array
     */
    protected function rules()
    {
        return [];
    }

    /**
     * This function is called only when all validation passed.
     * Can return anything that can be serialized.
     *
     * @throws ResolveException On any failure.
     * @param array $parameters
     * @return mixed
     */
    protected abstract function doResolve($parameters);

    /**
     * Call this to resolve the request and get a Response instance
     *
     * @param string $name
     * @param array $parameters
     * @return Response
     */
    public function resolve($name, $parameters)
    {

        if (($errorMsg = $this->shouldResolve()) !== true) {
            return new Response($name, false, $errorMsg);
        }

        $validator = Validator::make($parameters, $this->rules());

        if ($validator->fails()) {
            return new Response($name, false, $validator->errors());
        }

        try {
            $result = $this->doResolve($parameters);
            return new Response($name, true, $result);
        } catch (ResolveException $e) {
            return new Response($name, false, $e->getMessage());
        }
    }
}