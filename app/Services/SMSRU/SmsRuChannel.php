<?php


namespace App\Services\SMSRU;


use Exception;
use RuntimeException;
use Illuminate\Notifications\Notification;

class SmsRuChannel
{
    /**
     * @throws Exception
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toSmsRu')) {
            $message = $notification->toSmsRu($notifiable);
        } else {
            throw new RuntimeException('Метод toSmsRu отсутствует.');
        }

        if (!$message->geTo()) {
            if (!$to = $notifiable->routeNotificationForSmsRu()) {
                throw new Exception('Уведомление не было отправлено. Номер телефона не был указан.', 400);
            }
            $message->to($to);
        }

        if (!$message->getFrom()) {
            $message->from(config('services.smsru.sender'));
        }

        SMSRU::send($message);
    }
}
