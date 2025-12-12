<?php

namespace App\Console\Commands\temp\Years_24_25;

use App\Models\GoogleProject;
use Illuminate\Console\Command;

class PeriodSaturdayRecoveryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:period-saturday-recovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $cache = cache()->driver('database');
        $projects = GoogleProject::query()
            ->where('is_active', true)
            ->whereIn('id', PeriodSaturdayAddCommand::IDS)
            ->get();

        /** @var GoogleProject $project */
        foreach ($projects as $project) {

            $key = PeriodSaturdayAddCommand::key($project->id);
            if ($cache->has($key)) {
                $period = $cache->get($key);
                sort($period);
                $project->period = $period;
                $project->save();
                $cache->delete($key);

                $this->line('saturday recovery for ' . $project->id);
            } else {
                $this->line('saturday skip for ' . $project->id);
            }

        }

    }
}
