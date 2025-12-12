<?php

namespace App\Console;

use App\Console\Commands\LoadAmoCalls;
use App\Console\Commands\LoadAmoPipelines;
use App\Console\Commands\LoadAmoSalesManagers;
use App\Console\Commands\LoadBitrixCalls;
use App\Console\Commands\LoadBitrixSalesManagers;
use App\Console\Commands\RobotAmoTokens;
use App\Console\Commands\RobotBitrixTokens;
use App\Console\Commands\RobotGoogleDailyReport;
use App\Console\Commands\SetJobsByTelegramGoogleDailyReports;
use App\Helpers\ProductionCalendar;
use App\Models\GoogleProject;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    public $integrationSchedule;
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        //logger('scheduler run');

        /**
         * AMOCRM
         */
        $schedule->command(RobotAmoTokens::class)->twiceDaily(4, 23);

        $schedule->command(LoadAmoCalls::class)->dailyAt('01:00'); // за вчера
        $schedule->command(LoadAmoCalls::class, ['--from' => now(), '--to' => now()])->hourly();

        $schedule->command(LoadAmoSalesManagers::class)->dailyAt('05:00');
        $schedule->command(LoadAmoPipelines::class)->dailyAt('05:15');
        /**
         * BITRIX24
         */
        $schedule->command(RobotBitrixTokens::class)->everyThirtyMinutes();

        $schedule->command(LoadBitrixCalls::class)->dailyAt('01:00'); // за вчера
        $schedule->command(LoadBitrixCalls::class, ['--from' => now(), '--to' => now()])->hourly();

        $schedule->command(LoadBitrixSalesManagers::class)->dailyAt('02:00');

        // Google Daily reports
        $projects = GoogleProject::where('is_active', true)->get();
        foreach ($projects as $project) {
            if (empty($project->sending_period)) {
                continue;
            }
            if (!$project->sending_include_holidays && ProductionCalendar::isHoliday(now())) {
                continue;
            }

            $schedule->command(RobotGoogleDailyReport::class, [$project->id])
                ->dailyAt(Carbon::parse($project->report_time, $project->timezone)->format('H:i'))
                ->days($project->sending_period)
                ->withoutOverlapping();
        }
//        $schedule->command(SetJobsByTelegramGoogleDailyReports::class)->dailyAt('00:30');

        // Load production calendar
        $schedule->call(function () {
            ProductionCalendar::make();
        })->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
