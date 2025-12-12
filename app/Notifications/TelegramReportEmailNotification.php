<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;


/**
 * @todo удалить, если не понадобится
 * @deprecated
 */
class TelegramReportEmailNotification extends Notify
{
    private string $reportableHTML;
    private string $inputFile;
    private string $projectName;

    /**
     * Create a new notification instance.
     *
     * @param string $reportableHTML
     */
    public function __construct(string $reportableHTML, string $projectName, string $inputFile)
    {
        parent::__construct();
        $this->reportableHTML = $reportableHTML;
        $this->projectName = $projectName;
        $this->inputFile = $inputFile;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        //Log::debug("Project: {$this->projectName}");
        //Log::debug("Reportable HTML: {$this->reportableHTML}");
        //Log::debug("Input File: {$this->inputFile}");
        return (new MailMessage)
            ->subject("Телеграм рассылка для проекта: ".$this->projectName)
            ->line($this->reportableHTML)
            ->attach($this->inputFile);

    }

}
