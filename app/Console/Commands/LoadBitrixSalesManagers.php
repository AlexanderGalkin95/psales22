<?php

namespace App\Console\Commands;

use App\Jobs\DistributeIntegrationDataByProject;
use App\Models\Logs\LogEvent;
use Illuminate\Console\Command;
use App\Models\BitrixCode;
use App\Models\IntegrationSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use App\Services\Bitrix\Helpers\NotificationReports;
use App\Services\Bitrix\Notifications\BitrixReportable;
use App\Repositories\BitrixRepository;
use Illuminate\Support\Facades\Log;

class LoadBitrixSalesManagers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:managers {--bitrix_code_id= : id интеграции Bitrix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для получения менеджеров по продажам из Bitrix.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(BitrixRepository $bitrixRepository)
    {
        $logger = Log::channel('bitrix');
        $logger->info('start managers update');

        $reportableData = [];
        $bitrixCodes = BitrixCode::with('integration.projects')->whereHas('bitrixToken', function (Builder $query) {
            $query->whereRaw("expires_in + interval '5 minutes' > now()");
        })
            ->when(
                $this->option('bitrix_code_id'),
                function ($query) {
                    $query->where('id', $this->option('bitrix_code_id'));
                }
            )
            ->get();

        $integrationSchedule = IntegrationSchedule::create([
            'type' => IntegrationSchedule::SCHEDULE_TYPE_MANAGERS
        ]);

        $successCount = 0;
        $errorCount = 0;

        $bitrixCodes->each(function ($bitrixCode) use (&$reportableData, $bitrixRepository, $integrationSchedule, $logger, &$errorCount, &$successCount) {
            try {
                $bitrixRepository->refreshSalesManagers($bitrixCode, $integrationSchedule->id);
                $successCount++;
            } catch (\Throwable $t) {
                //$reportableData[] = new BitrixReportable($bitrixCode->domain, $exception->getMessage());
                //$this->warn("[{$bitrixCode->domain}] {$exception->getMessage()}");
                //report($exception);
                $logger->error("update managers error for domain {$bitrixCode->domain}: " . $t->getMessage());
                $errorCount++;
            }
        });

        /*
        $reportableData = Arr::sort($reportableData, function ($item) {
            return $item->domain;
        });
        if (count($reportableData)) {
            $message = '[Bitrix] Менеджеры по продажам обновились с ошибками';
            NotificationReports::bitrixReportToAdmins($reportableData, $message);
            $this->error($message);
        } else {
            $message = '[Bitrix] Менеджеры по продажам обновились успешно';
            NotificationReports::bitrixReportToSysAdmin([], $message);
            $this->info($message);
        }
        */

        DistributeIntegrationDataByProject::dispatch($integrationSchedule);

        if ($errorCount) {
            LogEvent::create(
                LogEvent::TYPE_ERROR_IMPORT,
                "Ошибки при импорте менеджеров BitrixCrm-проектов. Успешно/ошибочно: $successCount/$errorCount. Подробности в логах.",
            );
        }

        $logger->info("finish managers update. success/error: $successCount/$errorCount");
        return 0;

        //return count($reportableData) ? 1 : 0;
    }
}
