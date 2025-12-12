<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\Task;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\QueryListTrait;
use Illuminate\Support\Facades\DB;

class TaskRepository extends BaseRepository
{
    use QueryListTrait;

    public function generateTask(Project $project)
    {
        [$sortedCalls, $duration] = $this->filterCallsByManager($project);

        if (empty($sortedCalls) || !$duration) return null;

        return $this->saveGeneratedTask($project, $sortedCalls, $duration);
    }

    public function saveGeneratedTask(Project $project, array $calls, $duration = 0)
    {
        $attributes = [
            'project_id' => $project->id,
            'assessor_id' => $project->assessor->id,
            'total_duration' => $duration,
            'status' => Task::STATUS_CREATED
        ];

        $task = null;

        DB::transaction(function () use ($attributes, $calls, &$task) {
            $task = $this->create($attributes);

            $attachable = [];
            foreach ($calls as $call) {
                $attachable[$call->pivot->id] = ['duration' => $this->convertToMinutes($call->record_duration)];
            }

            $task->calls()->attach($attachable);

            $task = $task->fresh(['calls', 'assessor']);
        });

        return $task;
    }

    public function selectCalls(array $calls, $limit = 120)
    {
        $processableTime = 0;
        $sorted = [];
        foreach ($calls as $call) {
            if ($limit && $processableTime >= $limit) {
                break;
            }
            $sorted[] = $call;
            $processableTime += $this->convertToMinutes($call->record_duration);
        }

        return [$sorted, $processableTime];
    }

    public function filterCallsByManager(Project $project)
    {
        $managers = $project->callSettingsSalesManagers;
        $permissibleTotal = $project->total_time_limit + ($project->total_time_limit * ($project->permissible_error / 100));
        $calls = [];
        $totalDuration = 0;
        foreach ($managers as $manager) {
            $mCalls = $project->calls
                ->filter(
                    function ($value, $key) use ($manager) {
                        return $value->record_responsible_id === $manager->salesManager->foreign_manager_id;
                    }
                )->all();

            [$sorted, $processableTime] = $this->selectCalls($mCalls, $manager->duration_limit);

            $totalDuration += $processableTime;
            $calls = array_merge($calls, $sorted);

            if ($totalDuration >= $permissibleTotal) {
                break;
            }
        }

        return [$calls, $totalDuration];
    }

    public function convertToMinutes($duration): float
    {
        if (!$duration) return 0.00;
        $parsed = explode(':', $duration);
        return round(((int) $parsed[0] * 60) + (int) $parsed[1] + ( (int) $parsed[2] / 60), 2);
    }

    public function tasksList($request)
    {
        $user = $request->user();
        // TODO:: Получить задания по конкретному юзеру (асессору).
        $limit  = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);

        $mapping = [];

        $query = $this->model
            ->with([
                'assessor'
            ])
            ->withCount('calls')
            ->withAggregate('project as project_name', 'name')
            ->when(
                $request->has('$filter'),
                $this->applyFilterClosure($request, $mapping)
            );

        $total = $this->getCount($query);

        $tasks = $query
            ->when(
                $request->has('$orderBy'),
                $this->applyOrderByClosure($request, $mapping)
            )
            ->skip($offset)
            ->take($limit)
            ->get();

        return [
            'total' => $total,
            'tasks' => $tasks
        ];
    }

    public function task($taskId)
    {
        return $this->findById($taskId, null, ['calls']);
    }

    public function taskHistory($taskId)
    {
        return $this->findById($taskId)->history()->with('author')->get();
    }

    public function model(): string
    {
        return Task::class;
    }
}
