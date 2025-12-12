<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class AmoCRMConnectionErrorNotification extends Notify
{
    private $amoAccount;

    private string $projectName;

    /**
     * Create a new notification instance.
     *
     * @param $amoAccount
     * @param $projectName
     */
    public function __construct($amoAccount, $projectName)
    {
        $this->amoAccount = $amoAccount;
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
            ->subject("[{$this->amoAccount->domain}]: Связь с AmoCRM потеряна!")
            ->line("Вы получаете это сообщение в связи с тем, что сервер не может установить связь с AmoCRM ({$this->amoAccount->domain}) по проекту '{$this->projectName}'.");
    }
}
