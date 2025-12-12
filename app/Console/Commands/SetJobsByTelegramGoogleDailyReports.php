<?php

namespace App\Console\Commands;

use App\Helpers\ProductionCalendar;
use App\Jobs\SendTelegramGoogleDailyReport;
use App\Models\GoogleProject;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SetJobsByTelegramGoogleDailyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:set-reports-jobs';

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

        $projects = GoogleProject::where('is_active', true)->get();

        /** @var GoogleProject $project */
        foreach ($projects as $project) {
            if (empty($project->sending_period)) {
                continue;
            }
            if (!$project->sending_include_holidays && ProductionCalendar::isHoliday(now())) {
                continue;
            }

            $reportTime = Carbon::parse($project->report_time, $project->timezone);
            if ($reportTime->lessThan(now())) {
                continue;
            }

            $weekDays = $project->sending_period;
            if (!in_array(now()->dayOfWeek, $weekDays)) {
                continue;
            }

            SendTelegramGoogleDailyReport::dispatch($project->id)->delay($reportTime);

            logger()->info("report job for project {$project->name} {$project->id} is setted at " . $reportTime->format('Y-m-d H:i:s'));
        }

        return 0;
    }
}
