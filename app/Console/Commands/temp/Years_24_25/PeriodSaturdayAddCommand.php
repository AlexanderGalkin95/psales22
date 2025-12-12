<?php

namespace App\Console\Commands\temp\Years_24_25;

use App\Models\GoogleProject;
use Illuminate\Console\Command;

/**
 * Скрипт изменения рабочих дней недели для определенных проектов.
 * В рабочие дни добавляется суббота, если ее там нет.
 */
class PeriodSaturdayAddCommand extends Command
{

    const IDS = [590,602,150,600,586,6,25,593,585,595,54,176,603,588,601,578,482,467,480,581,605,470,57,538,483,565,530,613,616,532,117,611,88,485,680,681,106,10,31,50,684,490,683,623,626,143,689,77,80,627,200,486,620,284,499,16,167,624,230,213,157,194,501,208,630,573,567,636,632,555,637,58,690,558,327,645,380,640,639,638,642,382,514,647,692,392,651,399,550,648,421,652,425,657,412,656,411,444,456,663,661,527,526,450,662,574,453,670,673,677,667,669,676,671];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:period-saturday-add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public static function key($id): string
    {
        return 'google_projects-period-' . $id;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $cache = cache()->driver('database');
        $projects = GoogleProject::query()
            ->where('is_active', true)
            ->whereIn('id', self::IDS)
            ->get();

        /** @var GoogleProject $project */
        foreach ($projects as $project) {

            $period = (array)$project->period;

            // в любом случае сохраняем старые значения
            $key = self::key($project->id);
            $cache->put($key, $period);

            if (!in_array(6, $period)) {
                $period[] = 6;
                sort($period);
                $project->period = $period;
                $project->save();

                $this->line('saturday added for ' . $project->id);
            } else {
                $this->line('saturday already exists for ' . $project->id);
            }

        }

    }
}
