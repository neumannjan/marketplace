<?php

namespace App\Notifications;

use App\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification for a message that has been sent
 *
 * @package App\Notifications
 */
class MessageNotification extends LocalizedMailNotification
{
    use Queueable;

    /**
     * @var Message
     */
    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting($this->__('greeting'))
            ->line($this->__('line1'))
            ->action($this->__('action'), route('app'));
    }

    /**
     * @inheritDoc
     */
    protected function getKeyBase()
    {
        return $this->message->offer_id !== null ? 'email.message-offer'
            : 'email.message';
    }

    /**
     * @inheritDoc
     */
    protected function getLocalizationParameters()
    {
        return [
            'site' => config('app.name'),
            'from' => $this->message->from->display_name,
            'offer' => $this->message->offer_id !== null
                ? $this->message->offer->name : null,
        ];
    }
}
