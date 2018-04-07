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

        $localeInfo = include app()->path('locales.php');

        $array['messages'] = [];
        $array['locale_names'] = [];
        foreach (config('app.available_locales') as $lang) {
            $validationMessages = include app()->resourcePath("lang/$lang/validation.php");
            $interfaceMessages = include app()->resourcePath("lang/$lang/interface.php");

            $array['messages']["$lang.validation"] = [
                'min' => $validationMessages['min']['string'],
                'max' => $validationMessages['max']['string'],
                'maxArray' => $validationMessages['max']['array'],
                'required' => $validationMessages['required'],
                'slug' => $validationMessages['slug'],
                'numeric' => $validationMessages['numeric'],
                'containsNumeric' => $validationMessages['contains']['numeric'],
                'containsNonNumeric' => $validationMessages['contains']['non_numeric'],
                'confirmed' => $validationMessages['confirmed'],
                'email' => $validationMessages['email'],
                'image' => $validationMessages['image'],
            ];

            $array['messages']["$lang.interface"] = $interfaceMessages;

            $array['locale_names'][$lang] = $localeInfo[$lang]['native'];
        }

        $array['currency_default'] = config('app.currency');

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
