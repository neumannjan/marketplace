<?php

namespace App\Eloquent\Order;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model trait that allows for pagination based on order
 *
 * @method after(int | Model $model)
 */
trait OrderAware
{
    /**
     * Modifies the query to only return entries after a particular entry
     * @param Builder $query
     * @param Model|mixed $model Model instance or value of primary key
     * @return Builder
     */
    public function scopeAfter(Builder $query, $model)
    {
        $timestampOrder = $this->getOrderBy();

        if (!$timestampOrder) {
            throw new \InvalidArgumentException("getTimestampOrderBy() must return a string or a non-empty array of strings");
        }

        $query->withoutGlobalScope('order');

        if (!($model instanceof Model)) {
            $model = self::find($model);
        }

        // Example of what is happening below:
        // WHERE listed_at < timestamp OR
        // (listed_at = timestamp AND created_at < timestamp) OR
        // (listed_at = timestamp AND created_at = timestamp AND id < id)
        $query->whereNested(function ($query) use ($timestampOrder, $model) {
            /** @var Builder $query */

            $prev = [];
            foreach ($timestampOrder as $order) {
                $query->whereNested(function ($query) use ($order, $prev, $model) {
                    /** @var Builder $query */
                    $query->where($order, '<', $model->getAttribute($order));

                    foreach ($prev as $prevOrder) {
                        $query->where($prevOrder, '=', $model->getAttribute($prevOrder));
                    }

                    return $query;
                }, 'or');

                $prev[] = $order;
            }
        });

        foreach ($timestampOrder as $order) {
            $query->orderBy($order, 'desc');
        }

        return $query;
    }

    /**
     * @return string[]|string
     */
    function getOrderBy()
    {
        return ['created_at', 'id'];
    }
}