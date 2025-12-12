<?php

namespace App\Console\Commands;

use App\Jobs\DistributeIntegrationDataByProject;
use App\Models\Logs\LogEvent;
use Illuminate\Console\Command;
use App\Models\AmoCode;
use App\Models\IntegrationSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use App\Services\AmoCRM\Helpers\NotificationReports;
use App\Services\AmoCRM\Notifications\AmoCRMReportable;
use App\Repositories\AmoRepository;
use Illuminate\Support\Facades\Log;

class LoadAmoSalesManagers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:managers {--amo_code_id= : id интеграции AmoCRM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для получения менеджеров по продажам из AmoCRM.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(AmoRepository $amoRepository)
    {
        $logger = Log::channel('amo');
        $logger->info('start managers update');

        $reportableData = [];
        $amoCodes = AmoCode::with('integration.projects')->whereHas('amoToken', function (Builder $query) {
            $query->whereRaw("expires_in + interval '5 minutes' > now()");
        })
            ->when(
                $this->option('amo_code_id'),
                function ($query) {
                    $query->where('id', $this->option('amo_code_id'));
                }
            )
            ->get();

        $integrationSchedule = IntegrationSchedule::create([
            'type' => IntegrationSchedule::SCHEDULE_TYPE_MANAGERS
        ]);

        $successCount = 0;
        $errorCount = 0;

        $amoCodes->each(function ($amoCode) use (&$reportableData, $amoRepository, $integrationSchedule, $logger, &$errorCount, &$successCount) {
            try {
                $amoRepository->refreshSalesManagers($amoCode, $integrationSchedule->id);
                $successCount++;
            } catch (\Throwable $t) {
                //$reportableData[] = new AmoCRMReportable($amoCode->domain, $exception->getMessage());
                //$this->warn("[{$amoCode->domain}] {$exception->getMessage()}");
                //report($exception);
                $logger->error("update managers error for domain {$amoCode->domain}: " . $t->getMessage());
                $errorCount++;
            }
        });

        /*
        $reportableData = Arr::sort($reportableData, function ($item) {
            return $item->domain;
        });
        if (count($reportableData)) {
            $message = '[AmoCRM] Менеджеры по продажам обновились с ошибками';
            NotificationReports::amoCRMReportToAdmins($reportableData, $message);
            $this->error($message);
        } else {
            $message = '[AmoCRM] Менеджеры по продажам обновились успешно';
            NotificationReports::amoCRMReportToSysAdmin([], $message);
            $this->info($message);
        }
        */

        DistributeIntegrationDataByProject::dispatch($integrationSchedule);

        if ($errorCount) {
            LogEvent::create(
                LogEvent::TYPE_ERROR_IMPORT,
                "Ошибки при импорте менеджеров AmoCrm-проектов. Успешно/ошибочно: $successCount/$errorCount. Подробности в логах.",
            );
        }

        $logger->info("finish managers update. success/error: $successCount/$errorCount");
        return 0;

        //return count($reportableData) ? 1 : 0;
    }
}
