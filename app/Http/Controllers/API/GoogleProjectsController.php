<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetGoogleProjectsRequest;
use App\Http\Requests\StoreGoogleProjectRequest;
use App\Http\Requests\TestGoogleProjectRequest;
use App\Http\Requests\UpdateGoogleProjectRequest;
use App\Models\GoogleProject;
use App\Models\TelegramBot;
use App\Models\TelegramBotReport;
use App\Models\TelegramBotReportQueue;
use App\Notifications\TelegramGoogleDailyReport;
use App\Services\Google\GoogleService;
use App\Traits\QueryListTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GoogleProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param GetGoogleProjectsRequest $request
     * @return JsonResponse
     */

    use QueryListTrait;

    const REPORT_TIME_DIFFERENCE = 5;

    public function index(GetGoogleProjectsRequest $request): JsonResponse
    {
        $mapping = [
            'name' => 'google_projects.name',
            'telegram' => 'google_projects.telegram',
            'report_time'=> 'google_projects.report_time',
            'table_switch' => 'google_projects.is_active',
            'managers' => 'google_projects.managers',
        ];
        $limit  = $request->input('limit', 9999);
        $offset = $request->input('offset', 0);

        $query = GoogleProject::when(
                $request->has('$filter'),
                $this->applyFilterClosure($request, $mapping)
            )
            ->when(
                $request->has('search'),
                function ($q) use ($request) {
                    return $q->where(function ($q) use ($request) {
                        $q->Where('google_projects.name', 'ilike', '%' . $request->search . '%')
                            ->orWhere('google_projects.telegram', 'ilike', '%' . $request->search . '%')
                            ->orWhere('google_projects.report_time', 'ilike', '%' . $request->search . '%')
                            ->orWhere('google_projects.managers', 'ilike', '%' . $request->search . '%');
                    });
                }
            );

        $total = $this->getCount($query);
        $projects = $query->when(
            $request->has('$orderBy'),
            $this->applyOrderByClosure($request, array_merge($mapping, ['reference_name'   => 'ri.name']))
        )
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json(['google_projects' => $projects, 'total' => $total]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreGoogleProjectRequest $request
     * @return JsonResponse
     */
    public function store(StoreGoogleProjectRequest $request): JsonResponse
    {
        if (!$request->user()->hasRole(['sa', 'pm', 'senior_assessor'])) {
            abort(403, 'Метод недоступен для Вашей роли.');
        }
        $time = $request->input('report_time');
        $telegramForCheck = $request->input('telegram');
        if (!self::checkTimeDifference($time, $telegramForCheck)) {
            return response()->json([
                'error_type' => 'time',
                'message' => 'К данному телеграм боту уже привязан другой проект. Для корректной отправки отчёта необходимо выбирать время отправки с разницей в 5 минут.'
            ], 400);
        }

        $project = GoogleProject::create($request->all());
        $project->refresh();

        $telegram = TelegramBot::where('username', $request->telegram)->first();
        if (!$telegram) {
            $project->update(['telegram_bot_id' => null]);
        } else {
            $project->update(['telegram_bot_id' => $telegram->id]);
        }

        $project->refresh();
        return response()->json(compact('project'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $projectId
     * @return JsonResponse
     */
    public function show(int $projectId): JsonResponse
    {
        return response()->json(GoogleProject::find($projectId));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateGoogleProjectRequest $request
     * @param  int  $projectId
     * @return JsonResponse
     */
    public function update(UpdateGoogleProjectRequest $request, int  $projectId): JsonResponse
    {
        if (!$request->user()->hasRole(['sa', 'pm', 'senior_assessor'])) {
            abort(403, 'Метод недоступен для Вашей роли.');
        }
        $time = $request->input('report_time');
        $telegramForCheck = $request->input('telegram');
        if (!self::checkTimeDifference($time, $telegramForCheck, $projectId)) {
            return response()->json([
                'error_type' => 'time',
                'message' => 'К данному телеграм боту уже привязан другой проект. Для корректной отправки отчёта необходимо выбирать время отправки с разницей в 5 минут.'
            ], 400);
        }

        $project = GoogleProject::find($projectId);
        $project->update($request->only(['name', 'google_spreadsheet_id', 'report_time', 'managers', 'telegram', 'include_holidays', 'period', 'timezone', 'sending_period', 'sending_include_holidays', 'is_active', 'override_report_sent_at']));

        $telegram = TelegramBot::where('username', $request->telegram)->first();
        if (!$telegram) {
            $project->update(['telegram_bot_id' => null]);
        } else {
            $project->update(['telegram_bot_id' => $telegram->id]);
        }

        $project->refresh();
        return response()->json(compact('project'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param GoogleProject $googleProject
     * @return JsonResponse
     */
    public function destroy(GoogleProject $googleProject, int  $projectId): JsonResponse
    {
        if (!Auth::user()->hasRole(['sa', 'pm'])) {
            abort(403, 'Метод недоступен для Вашей роли.');
        }

        $project = GoogleProject::find($projectId);
        if ($project) {
            $project->delete();
        }
        return response()->json([
            'message' => "Проект [$project->name] был удалён успешно",
        ]);
    }

    /**
     *
     * @param TestGoogleProjectRequest $request
     * @param  int  $projectId
     * @return JsonResponse
     */
    public function sendReport(TestGoogleProjectRequest $request, int $projectId): JsonResponse
    {
        try {
            $googleService = new GoogleService();
            $googleService
                ->setSpreadsheetName($request->get('name'))
                ->setSpreadsheetId($request->get('google_spreadsheet_id'))
                ->setTabName('FG И CRM')
                ->validate();
            /** @var GoogleProject $project */
            $project = GoogleProject::find($projectId);
            if (!count($project->period)) {
                throw new Exception('Рабочие дни не выбраны.');
            }

            if (!$project->telegram_channel || $project->telegram_channel->status !== 'active') {
                throw new Exception('Телеграм не подключен. Попробуйте переподключить бот');
            }

            $botReport = new TelegramBotReport;
            $botReport->project_id = $projectId;
            $botReport->report_status = 'running';
            $botReport->save();
            $botReport->refresh();

            $dates = $project->getReportDates(true);
            if (empty($dates)) {
                $botReport->report_status = 'warning';
                $botReport->error_text = 'Дат для отчета не найдено';
                $botReport->save();
            }

            $countMessages = 0;
            foreach ($dates as $date) {
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
            }
        } catch (\Throwable $th) {
            report($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 400);
        }

        return response()->json([
            'message' => 'Подключение прошло успешно',
        ]);
    }

    private function checkTimeDifference($time, $telegram, $projectId = null): bool
    {
        $time = Carbon::parse($time);

        $timesForCheck = GoogleProject::where('telegram', $telegram)
            ->when($projectId, function($query) use ($projectId) {
                return $query->where('id', '<>' , $projectId);
            })
            ->pluck('report_time');

        foreach ($timesForCheck as $item) {
            $checkTime = Carbon::parse($item);
            $diffInMinutes = $time->diffInMinutes($checkTime);
            if ($diffInMinutes < self::REPORT_TIME_DIFFERENCE) {
                return false;
            }
        }
        return true;
    }
}
