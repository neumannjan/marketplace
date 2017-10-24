<?php

namespace App\Notifications;


use Illuminate\Notifications\Messages\MailMessage;

class ActivateRegistration extends LocalizedMailNotification
{
    public $token, $username, $email;

    /**
     * ActivateRegistration constructor.
     * @param $token
     * @param $username
     * @param $email
     */
    public function __construct($token, $username, $email)
    {
        $this->token = $token;
        $this->username = $username;
        $this->email = $email;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject($this->__('greeting'))
            ->greeting($this->__('greeting'))
            ->line($this->__('line1'))
            ->action($this->__('action'), url(route('user.activate', [
                'token' => $this->token,
                'username' => $this->username
            ])))
            ->line($this->__('line2'))
            ->line($this->__('line3'));
    }

    /**
     * @inheritDoc
     */
    protected function getKeyBase()
    {
        return 'email.register-activate';
    }

    /**
     * @inheritDoc
     */
    protected function getLocalizationParameters()
    {
        return [
            'site' => config('app.name'),
            'email' => $this->email,
            'username' => $this->username
        ];
    }
}