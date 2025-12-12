<?php

namespace App\Console\Commands;

use App\Jobs\DistributeIntegrationDataByProject;
use App\Models\AmoCallStatus;
use App\Models\AmoCode;
use App\Models\IntegrationSchedule;
use App\Models\Logs\LogEvent;
use App\Services\AmoCRM\Facades\AmoCRMHelper;
use App\Services\AmoCRM\Helpers\NotificationReports;
use App\Services\AmoCRM\Notifications\AmoCRMReportable;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class LoadAmoCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:calls {--from= : Дата начала} {--to= : Дата окончания} {--amo_code_id= : Id интеграции}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для получения звонков из AmoCRM.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $logger = Log::channel('amo');
        $logger->info('start calls update');

        $dateFormat = 'd.m.Y';

        $integrationSchedule = IntegrationSchedule::create([
            'type' => IntegrationSchedule::SCHEDULE_TYPE_CALLS
        ]);

        $amoCodes = AmoCode::with([
            'integration.projects' => function ($query) {
                $query->where('tasks_generation_status', true);
            }
        ])
            ->whereHas('amoToken', function (Builder $query) {
                $query->whereRaw("expires_in + interval '5 minutes' > now()");
            })
            ->when($this->option('amo_code_id'), function (Builder $query) {
                $query->where('id', (int)$this->option('amo_code_id'));
            })
            ->get();

        // Дата начала
        if (!empty($this->option('from'))) {
            $fromDate = Carbon::parse($this->option('from'));
            $fromDate = $fromDate->format($dateFormat);
        } else {
            $fromDate = Carbon::yesterday()->format($dateFormat);
        }

        // Дата оконачания
        if (!empty($this->option('to'))) {
            $toDate = Carbon::parse($this->option('to'));
            $toDate = $toDate->format($dateFormat);
        } else {
            $toDate = Carbon::yesterday()->format($dateFormat);
        }

        $params = [
            'filter' => [
                'notes_page' => 0,
                'call_type' => [AmoCode::CALL_IN, AmoCode::CALL_OUT],
                'filter_date_from' => $fromDate,
                'filter_date_to' => $toDate,
                'call_status' => [AmoCallStatus::CALL_SUCCESS],
            ]
        ];

        //$reportableData = [];

        $successCount = 0;
        $errorCount = 0;

        $amoCodes->each(function (AmoCode $amoCode) use ($params, $integrationSchedule, $logger, &$successCount, &$errorCount) {

            $projects = $amoCode->integration->projects;
            if (!$projects->count()) {
                $logger->info("integration domain [$amoCode->domain] has no projects");
                return;
            }
            try {
                AmoCRMHelper::runRequestCalls($amoCode, $params, $integrationSchedule->id);

                // подтягиваем дату начала импорта звонков до "неделю назад"
                foreach ($projects as $project) {
                    $project->date_start = now()->subWeek()->format('Y-m-d H:i:s');
                    $project->save();
                }

                $successCount++;
            } catch (\Throwable $t) {
                //$reportableData[] = new AmoCRMReportable($amoCode->domain, $exception->getMessage());
                $message = "update calls error for domain {$amoCode->domain}: " . $t->getMessage();
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
            $message = '[AmoCRM] Звонки обновились с ошибками';
            NotificationReports::amoCRMReportToAdmins($reportableData, $message);
            $this->error($message);
        } else {
            $message = '[AmoCRM] Звонки обновились успешно';
            NotificationReports::amoCRMReportToSysAdmin([], $message);
            $this->info($message);
        }
        */

        DistributeIntegrationDataByProject::dispatch($integrationSchedule);

        if ($errorCount) {
            LogEvent::create(
                LogEvent::TYPE_ERROR_IMPORT,
                "Ошибки при импорте звонков AmoCrm-проектов. Успешно/ошибочно: $successCount/$errorCount. Подробности в логах.",
            );
        }

        $logger->info("finish calls update. success/error: $successCount/$errorCount");
        return 0;
    }
}
