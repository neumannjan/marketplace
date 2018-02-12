<?php

namespace App\Eloquent\Order;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model interface that allows for pagination based on order
 */
interface OrderAwareModel
{
    /**
     * Modifies the query to only return entries after a particular entry
     * @param Builder $query
     * @param Model|mixed $model Model instance or value of primary key
     * @return Builder
     */
    public function scopeAfter(Builder $query, $model);

    /**
     * @return string[]|string
     */
    function getOrderBy();
}