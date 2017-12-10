<?php

namespace App\Api\Request;


use App\Api\Response\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * TODO documentation
 */
abstract class PaginatedRequest extends Request
{
    /**
     * Default value for `per_page` parameter. If `null`, the `per_page` parameter is always required.
     * @var int|null
     */
    protected $perPageDefault = 10;

    /**
     * @inheritDoc
     */
    protected function validateRules($parameters)
    {
        $rules = [
            'per_page' => ($this->perPageDefault === null ? 'required|' : '') . 'integer',
            'page' => 'integer',
        ] + $this->rules();

        $validator = Validator::make($parameters->all(), $rules);
        $validator->validate();
    }


    /**
     * @inheritDoc
     */
    protected function doResolve($name, Collection $parameters)
    {
        $perPage = $parameters->get('per_page', $this->perPageDefault);
        $page = $parameters->get('page', 1);

        $urlParameters = $this->urlParameters();
        $query = [
            'per_page' => $perPage
        ];

        foreach($urlParameters as $parameter) {
            $query[$parameter] = $parameters[$parameter];
        }

        $paginator = $this->getPaginator($parameters, $perPage, $page);
        $paginator->appends($query);
        $paginator->setPath(route('api.single', ['name' => $name], false));

        return new Response(true, $paginator);
    }

    /**
     * Returns an array of parameters that should be present in the URL get query in next/previous URLs
     * @return array
     */
    protected abstract function urlParameters();

    /**
     * Returns a Paginator instance to be used
     * @param Collection $parameters
     * @param integer $perPage
     * @param integer $page
     * @return Paginator
     */
    protected abstract function getPaginator(Collection $parameters, $perPage, $page);
}