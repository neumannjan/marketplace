<?php

namespace App\Notifications;


use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class LocalizedMailNotification extends Notification
{

    public function via()
    {
        return ['mail'];
    }

    /**
     * @var mixed $notifiable
     * @return MailMessage
     */
    public abstract function toMail($notifiable);

    /**
     * @return array
     */
    protected abstract function getLocalizationParameters();

    /**
     * @return string
     */
    protected abstract function getKeyBase();

    protected function __($key)
    {
        return __($this->getKeyBase() . '.' . $key, $this->getLocalizationParameters());
    }
}