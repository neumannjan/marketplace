<?php

namespace App\Eloquent\Timestamp;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Eloquent model interface that allows for pagination based on a timestamp
 *
 * @property-read Carbon|mixed orderTimestamp
 * @method after(int | Carbon $timestamp)
 */
interface OrderAwareModel
{
    /**
     * @param Builder $query
     * @param int|Carbon $timestamp
     * @return Builder
     */
    public function scopeAfter(Builder $query, $timestamp);

    /**
     * @return Carbon|mixed
     */
    public function getOrderTimestampAttribute();

    /**
     * @return string
     */
    function getTimestampOrderBy();

    /**
     * @return string
     */
    function getTimestampOrderDirection();
}