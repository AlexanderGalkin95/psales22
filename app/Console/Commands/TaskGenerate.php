<?php

namespace App\Console\Commands;

use App\Repositories\Facades\ProjectRepository;
use App\Repositories\Facades\TaskRepository;
use Illuminate\Console\Command;

class TaskGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:generate { --project= : Идентификатор проекта }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для генерации задачи для проекта';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $projects = ProjectRepository::getModel()
            ->with([
                'callSettingsSalesManagers',
                'calls' => function ($query) {
                    $query->whereDoesntHave('tasks');
                }
            ])
            ->whereHas('calls', function ($query) {
                $query->whereDoesntHave('tasks');
            })
            ->whereHas('callSettingsSalesManagers', function ($query) {
                $query->whereHas('assessors');
            })
            ->get();

        $projects->each(function ($project) {
            TaskRepository::generateTask($project);
        });

        return 0;
    }
}
