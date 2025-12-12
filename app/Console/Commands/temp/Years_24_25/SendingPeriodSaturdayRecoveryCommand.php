<?php

namespace App\Console\Commands\temp\Years_24_25;

use App\Models\GoogleProject;
use Illuminate\Console\Command;

class SendingPeriodSaturdayRecoveryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:send-period-saturday-recovery';

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
        $projects = GoogleProject::query()->where('is_active', true)->get();

        /** @var GoogleProject $project */
        foreach ($projects as $project) {

            $key = SendingPeriodSaturdayAddCommand::key($project->id);
            if ($cache->has($key)) {
                $sendingPeriod = $cache->get($key);
                sort($sendingPeriod);
                $project->sending_period = $sendingPeriod;
                $project->save();
                $cache->delete($key);

                $this->line('saturday recovery for ' . $project->id);
            } else {
                $this->line('saturday skip for ' . $project->id);
            }
        }

    }
}
