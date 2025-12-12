<?php

namespace App\Notifications;

use App\Services\Bitrix\Notifications\BitrixReportable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class BitrixReportsNotification extends Notify
{
    private array $reportableData;

    private string $message;

    /**
     * Create a new notification instance.
     *
     * @param BitrixReportable[] $reportableData
     * @param string $message
     */
    public function __construct(array $reportableData, string $message)
    {
        $this->reportableData = $reportableData;
        $this->message = $message;
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
            ->subject("$this->message")
            ->line("$this->message.")
            ->line(
                new HtmlString(
                    view('mail.integration_daily_report', ['reportableData' => $this->reportableData])
                )
            );
    }

}
