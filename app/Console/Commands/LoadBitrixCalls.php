<?php

namespace App\Console\Commands;

use App\Jobs\DistributeIntegrationDataByProject;
use App\Models\BitrixCode;
use App\Models\IntegrationSchedule;
use App\Models\Logs\LogEvent;
use App\Services\Bitrix\Facades\BitrixHelper;
use App\Services\Bitrix\Helpers\NotificationReports;
use App\Services\Bitrix\Notifications\BitrixReportable;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class LoadBitrixCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:calls {--from= : Дата начала} {--to= : Дата окончания} {--bitrix_code_id= : Id интеграции}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для получения звонков из BitrixCRM.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $logger = Log::channel('bitrix');
        $logger->info('start calls update');

        $integrationSchedule = IntegrationSchedule::create([
            'type' => IntegrationSchedule::SCHEDULE_TYPE_CALLS
        ]);

        $bitrixCodes = BitrixCode::with([
            'integration.projects' => function ($query) {
                $query->where('tasks_generation_status', true);
            }
            ])
            ->whereHas('bitrixToken', function (Builder $query) {
                $query->whereRaw("expires_in + interval '5 minutes' > now()");
            })
            ->when($this->option('bitrix_code_id'), function (Builder $query) {
                $query->where('id', (int) $this->option('bitrix_code_id'));
            })
            ->get();

        // Дата начала
        if (!empty($this->option('from'))) {
            $date = Carbon::parse($this->option('from'));
            $startDate = $date->setTime(0, 0)->toIso8601String();
        } else {
            $startDate = Carbon::yesterday()->setTime(0, 0)->toIso8601String();
        }

        // Дата оконачания
        if (!empty($this->option('to'))) {
            $date = Carbon::parse($this->option('to'));
            $endDate = $date->setTime(23, 59, 59)->toIso8601String();
        } else {
            $endDate = Carbon::yesterday()->setTime(23, 59, 59)->toIso8601String();
        }

        $reportableData = [];
        $successCount = 0;
        $errorCount = 0;

        $bitrixCodes->each(function (BitrixCode $bitrixCode) use (&$reportableData, $startDate, $endDate, $integrationSchedule, $logger, &$errorCount, &$successCount) {
            $params = [
                'domain' => $bitrixCode->domain,
                'method' => 'voximplant.statistic.get',
                'query' => [
                    'SORT' => 'ID',
                    'ORDER' => 'DESC',
                    'FILTER' => [
                        ">CALL_START_DATE" => $startDate,
                        "<CALL_START_DATE" => $endDate,
                        "CALL_FAILED_CODE" => BitrixCode::CALL_SUCCESS,
                        "HAS_RECORD" => 'Y',
                    ],
                    'start' => 0
                ]
            ];

            $projects = $bitrixCode->integration->projects;
            if (!$projects->count()) {
                $logger->info("integration domain [$bitrixCode->domain] has no projects");
                return;
            }

            try {
                BitrixHelper::runRequestCalls($bitrixCode, $params, $integrationSchedule->id);

                // подтягиваем дату начала импорта звонков до "неделю назад"
                foreach ($projects as $project) {
                    $project->date_start = now()->subWeek()->format('Y-m-d H:i:s');
                    $project->save();
                }

                $successCount++;
            } catch (\Throwable $t) {
                //$reportableData[] = new BitrixReportable($bitrixCode->domain, $exception->getMessage());
                //$this->warn("[{$bitrixCode->domain}] {$exception->getMessage()}");
                //report($exception);
                $message = "update calls error for domain {$bitrixCode->domain}: " . $t->getMessage();
                $this->warn($message);
                $logger->error($message);
                $errorCount++;
            }
        });

        /*
        $reportableData = Arr::sort($reportableData, function ($item) {
            return $item->domain;
        });
        if (count($reportableData)) {
            $message = '[Bitrix24] Звонки обновились с ошибками';
            NotificationReports::bitrixReportToAdmins($reportableData, $message);
            $this->error($message);
        } else {
            $message = '[Bitrix24] Звонки обновились успешно';
            NotificationReports::bitrixReportToSysAdmin([], $message);
            $this->info($message);
        }
        */

        DistributeIntegrationDataByProject::dispatch($integrationSchedule);

        if ($errorCount) {
            LogEvent::create(
                LogEvent::TYPE_ERROR_IMPORT,
                "Ошибки при импорте звонков BitrixCrm-проектов. Успешно/ошибочно: $successCount/$errorCount. Подробности в логах.",
            );
        }

        $logger->info("finish calls update. success/error: $successCount/$errorCount");
        return 0;
    }
}
