<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class BitrixConnectionErrorNotification extends Notify
{
    private $bitrix;

    private string $projectName;

    /**
     * Create a new notification instance.
     *
     * @param $bitrix
     * @param $projectName
     */
    public function __construct($bitrix, $projectName)
    {
        $this->bitrix = $bitrix;
        $this->projectName = $projectName;
        parent::__construct();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->bitrix->domain}]: Связь с Bitrix24 потеряна!")
            ->line("Вы получаете это сообщение в связи с тем, что сервер не может установить связь с Bitrix24 ({$this->bitrix->domain}) по проекту '{$this->projectName}'.");
    }
}
