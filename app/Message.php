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
 * @property-read integer|null $user_username
 */
class Message extends Model implements AuthorizationAwareModel, OrderAwareModel
{
    use OrderAware;

    const SCOPE_PERSONAL = 'personal';
    const SCOPE_ANY = 'any';

    protected $fillable = [
        'from',
        'to',
        'from_username',
        'to_username',
        'content',
        'additional',
        'read',
        'identifier'
    ];

    protected $casts = [
        'additional' => 'array'
    ];

    protected $appends = ['identifier'];

    /**
     * Message identifier. Provided by the client. Not saved to the database.
     * @var string
     */
    protected $identifier;

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

    public function getIdentifierAttribute()
    {
        return $this->identifier;
    }

    public function setIdentifierAttribute($identifier)
    {
        $this->identifier = $identifier;
    }

    public function from()
    {
        return $this->belongsTo(User::class, 'from_username', 'username');
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'to_username', 'username');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_username', 'username');
    }

    public function scopeConversationsWith(Builder $query, $user_username)
    {
        /*
         * SELECT messages.*, m.user_username
         * FROM messages
         *   INNER JOIN (
         *                SELECT
         *                  IF(to_username = 1, from_username, to_username) AS user_username,
         *                  MAX(id)                       AS max_id
         *                FROM messages
         *                WHERE to_username = 1 OR from_username = 1
         *                GROUP BY user_username
         *              ) m
         *     ON messages.id = m.max_id
         * ORDER BY messages.created_at DESC, messages.id DESC;
         */

        $inner = Message::query()
            ->withoutGlobalScope('order')
            ->select([
                DB::raw("IF(to_username = ?, from_username, to_username) AS user_username"),
                DB::raw('MAX(id) AS max_id')
            ])
            ->addBinding($user_username, 'select')
            ->where(['to_username' => $user_username])
            ->orWhere(['from_username' => $user_username])
            ->groupBy(['user_username']);

        return $query
            ->select(['messages.*', 'm.user_username'])
            ->join(DB::raw("({$inner->toSql()}) m"), 'messages.id', '=', 'm.max_id')
            ->addBinding($inner->getBindings(), 'join');
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
        $user = Auth::user();

        if ($user === null) {
            throw new \RuntimeException("Not logged in");
        }

        $userQuery = function ($query) {
            /** @var Builder $query */
            return $query->where(['status' => User::STATUS_ACTIVE]);
        };

        return $query
            ->whereNested(function ($query) use ($user) {
                $username = $user->username;

                /** @var Builder $query */
                return $query
                    ->where(['to_username' => $username])
                    ->orWhere(['from_username' => $username]);
            })
            ->whereHas('from', $userQuery)
            ->whereHas('to', $userQuery);
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
                        'from_username',
                        'to_username',
                        'content',
                        'additional',
                    ]))
                    ->isEmpty();
        }

        return false;
    }
}
