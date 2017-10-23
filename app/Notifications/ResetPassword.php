<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends LocalizedMailNotification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * ResetPassword constructor.
     * @param $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->line($this->__('line1'))
            ->action($this->__('action'), url(route('password.reset', $this->token)))
            ->line($this->__('line2'));
    }

    /**
     * @inheritDoc
     */
    protected function getKeyBase()
    {
        return 'email.password-reset';
    }

    /**
     * @inheritDoc
     */
    protected function getLocalizationParameters()
    {
        return [];
    }

}