<?php

namespace App\Console\Commands;

use App\Models\GoogleProject;
use App\Models\Logs\LogEvent;
use App\Models\TelegramBotReport;
use App\Models\TelegramBotReportQueue;
use App\Notifications\TelegramGoogleDailyReport;
use App\Services\Google\GoogleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RobotGoogleDailyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:report { projectId : Идентификатор проекта }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для автоматического генерации и отправки ежедневного отчета в телеграм';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $logger = Log::channel('telegram-report');
        $logger->info('start daily report');

        $projectId = (int)$this->argument('projectId');
        /** @var GoogleProject $project */
        $project = GoogleProject::find($projectId);

        if (!$project) {
            //$this->error("Проект [$project->name] не найден");
            $logger->info("finish. project #{$projectId} not found");
            return 0;
        }

        if (!$project->telegram_channel || $project->telegram_channel->status !== 'active') {
            //$this->error("Телеграм канал для проекта [$project->name] не найден");
            $logger->info("finish. telegram channel dor project {$project->name} not found");
            return 0;
        }

        $botReport = new TelegramBotReport;
        $botReport->project_id = $projectId;
        $botReport->report_status = 'running';
        $botReport->save();
        $botReport->refresh();

        $dates = $project->getReportDates();


        if (empty($dates)) {
            $logger->info('Telegram Report - Project ID: ' . $projectId . ' Dates: Not Found!');
            $botReport->report_status = 'warning';
            $botReport->error_text = 'Дат для отчета не найдено';
            $botReport->save();
            return 0;
        }

        $dateStrings = array_map(function ($date) {
            return $date->format('Y-m-d');
        }, $dates);

        $logger->info('Telegram Report - Project ID: ' . $projectId . ' Dates: ' . implode(', ', $dateStrings));

        $countMessages = 0;
        foreach ($dates as $date) {
            $tryCount = 0;
            do {
                $tryCount++;
                $result = false;
                try {
                    $googleService = new GoogleService();
                    $googleService
                        ->setSpreadsheetName($project->name)
                        ->setSpreadsheetId($project->google_spreadsheet_id)
                        ->setTabName('FG И CRM')
                        ->validate();
                    $data = $googleService->getDataForDailyReport($project, $date);

                    $reportQueue = new TelegramBotReportQueue;
                    $reportQueue->report_status = 'running';
                    $reportQueue->report_id = $botReport->id;
                    $reportQueue->save();
                    $reportQueue->refresh();
                    $managersData = $data['rating'];
                    $isFirst = true;

                    if (count($managersData)) {
                        foreach ($managersData as $managerData) {
                            $message = new TelegramGoogleDailyReport($data, $project, $date, $reportQueue, $isFirst, $managerData);
                            $project->notify($message->delay(now()->addSeconds($countMessages * 5)));
                            $countMessages++;
                            $isFirst = false;
                        }
                    } else {
                        $message = new TelegramGoogleDailyReport($data, $project, $date, $reportQueue);
                        $project->notify($message->delay(now()->addSeconds($countMessages * 5)));
                        $countMessages++;
                    }

                    $msg = "report for project [$project->name] on date [$date] added to queue";
                    $this->info($msg);
                    $logger->info($msg);
                    $result = true; // маркер успешного выполнения
                } catch (\Throwable $t) {
                    $msg = "report error for project [$project->name] on date [$date], tryNum=$tryCount : " . $t->getMessage();
                    $this->error($msg);
                    $logger->error($msg);
                    sleep(10);
                }

            } while (!$result && ($tryCount <= 3));

            // если ошибка после всех раз
            if (!$result) {
                LogEvent::create(
                    LogEvent::TYPE_ERROR_TELEGRAM,
                    "Проект: {$project->name} (#{$project->id}). Ошибка: " . ($t ? $t->getMessage() : 'неизвестно'),
                );
                $botReport->report_status = 'error';
                $botReport->error_text .= $t->getMessage();
                $botReport->save();
                return 0;
            }

        }

        $botReport->last_report_sent_at = DB::raw('NOW()');
        $botReport->report_status = 'queued';
        $botReport->save();

        return 0;
    }
}
