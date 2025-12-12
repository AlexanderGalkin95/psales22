<?php

namespace App\Notifications;

use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notify
{
    public string $context;

    public string $title;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($context, $title)
    {
        $this->context = $context;
        $this->title = $title;
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
        return [TelegramChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            ->to($notifiable->chat_id)
            ->content($this->buildText())
            ->options([
                'disable_web_page_preview' => true,
                'parse_mode' => 'html',
            ]);
    }

    public function buildText(): string
    {
        $text = "<b>$this->title</b>\n\n";
        $text .= $this->context;

        return $text;
    }
}
