<?php

namespace App\Api\Request;

use App\Http\Resources\Conversation;
use App\Message;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request as HttpRequest;

/**
 * Request that contains variables that the frontend might require at the beginning of its existence.
 * Extends {@see GlobalRequest}.
 */
class InitialDataRequest extends GlobalDataRequest
{
    public static function get(HttpRequest $request)
    {
        $array = parent::get($request);

        $array['messages'] = [
            'validation' => [
                'min' => trans('validation.min.string'),
                'max' => trans('validation.max.string'),
                'maxArray' => trans('validation.max.array'),
                'required' => trans('validation.required'),
                'slug' => trans('validation.slug'),
                'numeric' => trans('validation.numeric'),
                'containsNumeric' => trans('validation.contains.numeric'),
                'containsNonNumeric' => trans('validation.contains.non_numeric'),
                'confirmed' => trans('validation.confirmed'),
                'email' => trans('validation.email'),
                'image' => trans('validation.image'),
            ]
        ];

        /** @var User $user */
        $user = \Auth::user();

        // unread conversations
        if ($user) {
            /** @var Builder $query */
            $query = Message::conversationsWith($user->username)
                ->scopes(['personal']);
            $conversations = $query
                ->where(['to_username' => $user->username])
                ->where(['read' => false])
                ->get();

            $array['unread_conversations'] = Conversation::collection($conversations);
        }

        // upload limits
        $array['max_file_uploads'] = (int) ini_get('max_file_uploads');

        return $array;
    }
}
