<?php

namespace App\Notifications;

use App\Models\GoogleProject;
use App\Models\Logs\LogEvent;
use App\Models\TelegramBotReportQueue;
use App\Services\HtmlToImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramGoogleDailyReport extends Notification implements ShouldQueue
{
    use Queueable;

    public GoogleProject $project;

    public string $chatId = '';

    public string $imagePath = '';

    public string $tmpHtml = '';

    public string $reportDate = '';

    public array $viewData = [];

    private TelegramBotReportQueue $reportQueue;

    public array $managerData;

    public bool $isFirst;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        array $viewData,
        GoogleProject $project,
        $date,
        TelegramBotReportQueue $reportQueue,
        $isFirst = false,
        $managerData = []
    ) {
        $this->connection = 'telegram';

        $this->project = $project;
        $this->chatId = $project->telegram_channel->chat_id;
        $this->reportDate = $date->format('d.m.Y');
        $this->imagePath = public_path('images/report_' .
            $project->id . '_' .
            $date->format('Ymd') . '_' .
            rand(1000000, 9999999) . '.png');
        $this->viewData = $viewData;
        $this->tmpHtml = new HtmlString(
            view('reports.daily_report_table', [
                'report' => $this->viewData['report'] ?? [],
                'criterias' => $this->viewData['criterias'] ?? [],
                'reportDate' => $this->reportDate,
            ])
        );
        $this->reportQueue = $reportQueue;
        $this->managerData = $managerData;
        $this->isFirst = $isFirst;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toTelegram($notifiable)
    {
        $service = null;

        if ($this->isFirst || empty($this->managerData)) {

            $service = new HtmlToImageService();
            $service->setHtml($this->tmpHtml);
            $this->imagePath = $service->handle();

            try {
                Telegram::sendPhoto([
                    'chat_id' => $this->chatId,
                    'photo' => InputFile::create($this->imagePath),
                    'caption' => 'Добрый день!☀️' . PHP_EOL . 'Результаты оценки ' . $this->project->name . ' за ' . $this->reportDate . ' ↩️',
                ]);
            } catch (\Throwable $t) {
                LogEvent::create(
                    LogEvent::TYPE_ERROR_TELEGRAM,
                    "Проект: {$this->project->name} (#{$this->project->id}). Ошибка: {$t->getMessage()}",
                );
                return null;
            }


        }

        $managerDataForReport = $this->managerData;
        if (array_key_exists(1, $managerDataForReport)) {
            $managerDataForReport[1] = nl2br($managerDataForReport[1]);
        }

        if ($service) {
            $service->delFiles();
        }

        return TelegramMessage::create()
            ->to($notifiable->chat_id)
            ->view('reports.daily_report', [
                'rating' => $this->managerData,
            ])->options([
                'disable_web_page_preview' => true,
                'parse_mode' => 'html',
            ])
            ->chunk();
    }

    public function failed(\Throwable $e): void
    {
        $this->reportQueue->report_status = 'error';
        $this->reportQueue->error_text = $e->getMessage();
        $this->reportQueue->save();
    }
}
