<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class UserBlockedNotification extends Notify
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        $admin = config('mail.sysadmin_email_address');
        return (new MailMessage)
            ->subject('Уведомление о блокировке вашего аккаунта')
            ->greeting("Здравствуйте, $notifiable->name")
            ->line('Вы получили это сообщение в связи с тем, что ваш аккаунт был заблокирован.')
            ->line("Свяжитесь с администраторов $admin");
    }
}
