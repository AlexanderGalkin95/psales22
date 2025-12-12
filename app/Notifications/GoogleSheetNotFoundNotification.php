<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class GoogleSheetNotFoundNotification extends Notify
{
    protected $sheet;

    /**
     * Create a new notification instance.
     *
     * @param string $sheet
     */
    public function __construct(string $sheet)
    {
        $this->sheet = $sheet;
        parent::__construct();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Google лист '$this->sheet' недоступен для записи!")
            ->line("Вы получили это сообщение в связи с тем, что сервер не смог установить связь с листом '$this->sheet' в Google таблицах.");
    }
}
