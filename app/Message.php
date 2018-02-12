<?php

namespace App;

use App\Eloquent\AuthorizationAwareModel;
use App\Eloquent\Order\OrderAware;
use App\Eloquent\Order\OrderAwareModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @property-read integer|null $user_id
 */
class Message extends Model implements AuthorizationAwareModel, OrderAwareModel
{
    use OrderAware;

    const SCOPE_PERSONAL = 'personal';
    const SCOPE_ANY = 'any';

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
            return $query
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc');
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
        /*
         * SELECT messages.*, m.user_id
         * FROM messages
         *   INNER JOIN (
         *                SELECT
         *                  IF(to_id = 1, from_id, to_id) AS user_id,
         *                  MAX(id)                       AS max_id
         *                FROM messages
         *                WHERE to_id = 1 OR from_id = 1
         *                GROUP BY user_id
         *              ) m
         *     ON messages.id = m.max_id
         * ORDER BY messages.created_at DESC, messages.id DESC;
         */

        $inner = Message::query()
            ->withoutGlobalScope('order')
            ->select([
                DB::raw("IF(to_id = $user_id, from_id, to_id) AS user_id"),
                DB::raw('MAX(id) AS max_id')
            ])
            ->where(['to_id' => $user_id])
            ->orWhere(['from_id' => $user_id])
            ->groupBy(['user_id']);

        return $query
            ->setBindings($inner->getBindings())
            ->select(['messages.*', 'm.user_id'])
            ->join(DB::raw("({$inner->toSql()}) m"), 'messages.id', '=', 'm.max_id');
    }

    /**
     * @inheritDoc
     */
    public function getPublicScopes()
    {
        return [self::SCOPE_PERSONAL, self::SCOPE_ANY];
    }

    public function scopeAny(Builder $query)
    {
        return $query;
    }

    public function scopePersonal(Builder $query)
    {
        $id = Auth::id();

        if ($id === null) {
            throw new \RuntimeException("Not logged in");
        }

        return $query
            ->where(['to_id' => $id])
            ->orWhere(['from_id' => $id]);
    }

    /**
     * @inheritDoc
     */
    public function canUsePublicScope($scopeName, User $user = null)
    {
        switch ($scopeName) {
            case self::SCOPE_ANY:
                return $user->is_admin;
            case self::SCOPE_PERSONAL:
                return $user && \Auth::check() && $user->id === \Auth::id();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function validatePublicScopeParams($scopeName, $columnNames)
    {
        switch ($scopeName) {
            case self::SCOPE_ANY:
            case self::SCOPE_PERSONAL:
                return Collection::wrap($columnNames)
                    ->diff(Collection::make([
                        'from_id',
                        'to_id',
                        'content',
                        'additional',
                    ]))
                    ->isEmpty();
        }

        return false;
    }
}
