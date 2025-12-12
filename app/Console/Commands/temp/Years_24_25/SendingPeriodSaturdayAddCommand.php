<?php

namespace App\Console\Commands\temp\Years_24_25;

use App\Models\GoogleProject;
use Illuminate\Console\Command;

/**
 * Скрипт изменения расписания отправки телеграм-отчетов для определенных проектов.
 * В расписание отправки проектов добавляется суббота, если ее там нет.
 *
 */
class SendingPeriodSaturdayAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:send-period-saturday-add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public static function key($id): string
    {
        return 'google_projects-send_period-' . $id;
    }


    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $cache = cache()->driver('database');
        $projects = GoogleProject::query()->where('is_active', true)->get();

        /** @var GoogleProject $project */
        foreach ($projects as $project) {
            $sendingPeriod = (array)$project->sending_period;

            // в любом случае сохраняем старое значение
            $key = self::key($project->id);
            $cache->put($key, $sendingPeriod);

            // если у проекта нет отсылки в субботу 28го - добавляем субботу
            if (!in_array(6, $sendingPeriod)) {

                $sendingPeriod[] = 6;
                sort($sendingPeriod);
                $project->sending_period = $sendingPeriod;
                $project->save();

                $this->line('saturday added for ' . $project->id);
            } else {
                $this->line('saturday already exists for ' . $project->id);
            }
        }

    }
}
