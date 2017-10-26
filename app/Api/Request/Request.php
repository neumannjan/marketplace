<?php

namespace App\Api\Request;


use App\Api\Response\Response;
use Illuminate\Support\Facades\Validator;

abstract class Request
{

    /**
     * If the request should terminate with an error, returns the error message. Otherwise returns true.
     * @return true|string
     */
    protected function shouldResolve()
    {
        return true;
    }

    /**
     * @return array
     */
    protected function rules()
    {
        return [];
    }

    /**
     * @throws ResolveException
     * @param array $parameters
     * @return mixed
     */
    protected abstract function doResolve($parameters);

    /**
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