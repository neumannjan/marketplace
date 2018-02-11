<?php

namespace App\Eloquent\Timestamp;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model trait that allows for pagination based on a timestamp
 *
 * @property-read Carbon|mixed orderTimestamp
 * @method after(int | Carbon $timestamp)
 */
trait OrderAware
{
    /**
     * @param Builder $query
     * @param int|Carbon $timestamp
     * @return Builder
     */
    public function scopeAfter(Builder $query, $timestamp)
    {
        $timestampOrder = $this->getTimestampOrderBy();
        $timestampOrderDirection = $this->getTimestampOrderDirection();

        if (!$timestampOrder) {
            throw new \InvalidArgumentException("getTimestampOrderBy() must return a string");
        }

        $query->withoutGlobalScope('order');

        if (!($timestamp instanceof Carbon)) {
            $timestamp = Carbon::createFromTimestamp($timestamp);
        }

        $query->where($timestampOrder, $timestampOrderDirection === 'desc' ? '<' : '>', $timestamp);

        $query->orderBy($timestampOrder, $timestampOrderDirection);

        return $query;
    }

    public function getOrderTimestampAttribute()
    {
        $name = $this->getTimestampOrderBy();

        if (!$name) {
            throw new \InvalidArgumentException("getTimestampOrderBy() must return a string");
        }

        if ($this instanceof Model) {
            return $this->getAttribute($name);
        } else {
            throw new \RuntimeException("OrderAware has to be Model");
        }
    }

    /**
     * @return string
     */
    function getTimestampOrderBy()
    {
        return 'created_at';
    }

    /**
     * @return string
     */
    function getTimestampOrderDirection()
    {
        return 'desc';
    }
}