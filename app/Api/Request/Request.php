<?php

namespace App\Api\Request;


use App\Api\Response\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * API request class
 */
abstract class Request
{

    /**
     * Decides whether the request will be resolved.
     * If the request should not run, returns the error message. Otherwise returns true.
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
     * Should return a Response.
     *
     * @param $name
     * @param Collection $parameters
     * @return Response
     * @throws ValidationException
     */
    protected abstract function doResolve($name, Collection $parameters);

    /**
     * Call this to resolve the request and get a Response instance
     *
     * @param string $name
     * @param array $parameters
     * @return Response
     * @internal param $url
     */
    public final function resolve($name, $parameters)
    {
        if(!$parameters instanceof Collection)
            $parameters = Collection::make($parameters);

        try {
            if (($errorMsg = $this->shouldResolve()) !== true) {
                $response = new Response(false, $errorMsg);
                $response->setName($name);

                return $response;
            }

            $this->validateRules($parameters);

            $response = $this->doResolve($name, $parameters);
            $response->setName($name);

            return $response;
        } catch (ValidationException $e) {
            $response = new Response(false, [
                'validation' => $e->errors()
            ]);

            $response->setName($name);

            return $response;
        }
    }

    /**
     * @param Collection $parameters
     * @throws ValidationException
     */
    protected function validateRules($parameters)
    {
        $validator = Validator::make($parameters->all(), $this->rules());
        $validator->validate();
    }
}