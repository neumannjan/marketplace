<?php

namespace App\Api\Data;


use Illuminate\Session\Store;

/**
 * Class containing flash messages from the session.
 * It can be converted to an array passable to frontend.
 */
class FlashMessages implements Passable
{
    public $messages;

    /**
     * Frontend message type list
     */
    const TYPES
        = [
            'success',
            'status',
            'danger',
            'warning',
            'primary',
            'secondary',
        ];

    /**
     * FlashMessages constructor.
     */
    public function __construct()
    {
        /** @var Store $session */
        $session = \App::get('session');
        $this->messages = new \stdClass();

        foreach (self::TYPES as $type) {
            $messages = $session->get($type);

            if ( ! $messages) {
                continue;
            }

            if ($type === 'status') {
                $type = 'success';
            }

            foreach ($messages as $key => $message) {
                $this->messages->{"$type.$key"} = [
                    'type' => $type,
                    'message' => $message,
                ];
            }
        }
    }

    /**
     * Removes all flash messages from session
     */
    public function clearSession()
    {
        /** @var Store $session */
        $session = \App::get('session');

        foreach (self::TYPES as $type) {
            $session->remove($type);
        }
    }

    /**
     * @inheritDoc
     */
    public function toPassable()
    {
        return $this->messages;
    }
}