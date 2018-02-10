<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property-read integer|null $user_id
 */
class Message extends Model
{
    protected $fillable = [
        'from',
        'to',
        'content',
        'additional'
    ];

    protected $casts = [
        'additional' => 'array'
    ];

    /**
     * @inheritDoc
     */
    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope('order', function ($query) {
            /** @var Builder $query */
            return $query->orderBy('created_at', 'desc');
        });
    }

    public function from()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeConversationsWith(Builder $query, $user_id)
    {
        return $query
            ->select([
                DB::raw("IF(to_id = $user_id, from_id, to_id) as user_id"),
                DB::raw('ANY_VALUE(content) as content'),
                DB::raw('ANY_VALUE(created_at) as created_at'),
            ])
            ->where(['from_id' => $user_id])
            ->orWhere(['to_id' => $user_id])
            ->groupBy('user_id');
    }
}
