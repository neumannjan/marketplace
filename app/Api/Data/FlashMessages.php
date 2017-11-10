<?php

namespace App\Api\Data;


use Illuminate\Session\Store;

/**
 * Class containing flash messages from the session.
 * It can be converted to an array passable to
 */
class FlashMessages implements Passable
{
    public $messages;

    /**
     * FlashMessages constructor.
     */
    public function __construct()
    {
        /** @var Store $session */
        $session = \App::get('session');
        $this->messages = new \stdClass();

        foreach (['success', 'status', 'danger', 'warning', 'primary', 'secondary'] as $type) {
            $messages = $session->get($type);

            if (!$messages) {
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
     * @inheritDoc
     */
    public function toPassable()
    {
        return $this->messages;
    }
}