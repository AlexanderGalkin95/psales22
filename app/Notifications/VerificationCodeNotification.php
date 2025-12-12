<?php

namespace App\Notifications;

use App\Services\SMSRU\Message;
use App\Services\SMSRU\SmsRuChannel;

class VerificationCodeNotification extends Notify
{
    public int $smsCode;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($smsCode)
    {
        $this->smsCode = $smsCode;
        parent::__construct();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return [SmsRuChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toSmsRu($notifiable): Message
    {
        return (new Message())
                    ->to($notifiable->phone)
                    ->text($this->smsCode);
    }
}
