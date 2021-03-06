<?php

namespace App\Notifications;


use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail Notification class that simplifies
 * internationalization of content.
 */
abstract class LocalizedMailNotification extends Notification
{

    public function via()
    {
        return ['mail'];
    }

    /**
     * @param mixed $notifiable
     *
     * @return MailMessage
     */
    public abstract function toMail($notifiable);

    /**
     * Localization parameters
     * @return array
     */
    protected abstract function getLocalizationParameters();

    /**
     * Localization key base
     * @return string
     */
    protected abstract function getKeyBase();

    /**
     * Translate a message
     * @param $key
     *
     * @return array|null|string
     */
    protected function __($key)
    {
        return __($this->getKeyBase().'.'.$key,
            $this->getLocalizationParameters());
    }
}