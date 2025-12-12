<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateLoadTaskRequest;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function generateTask(Request $request, ProjectRepository $pr, TaskRepository $tr, $projectId)
    {
        $projects = $pr->getModel()
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
            ->where('tasks_generation_status', true)
            ->get();

        $tasks = [];

        $projects->each(function ($project) use ($tr, &$tasks) {
            if ($task = $tr->generateTask($project))
                $tasks[] = $task;
        });

        return response()->json($tasks, 200);
    }

    public function tasksList(Request $request, TaskRepository $taskRepository): JsonResponse
    {
        return response()->json($taskRepository->tasksList($request));
    }

    public function getTask(ValidateLoadTaskRequest $request, TaskRepository $taskRepository, $taskId): JsonResponse
    {
        return response()->json([
            'task' => $taskRepository->task($taskId),
            'status' => 'success'
        ]);
    }

    public function taskHistory(ValidateLoadTaskRequest $request, TaskRepository $taskRepository, $taskId): JsonResponse
    {
        return response()->json([
            'history' => $taskRepository->taskHistory($taskId),
            'status' => 'success'
        ]);
    }
}
