<?php

namespace App\Console\Commands;

use App\Models\BitrixToken;
use App\Models\Logs\LogEvent;
use App\Notifications\BitrixConnectionErrorNotification;
use App\Services\Bitrix\Helpers\NotificationReports;
use App\Services\Bitrix\Notifications\BitrixReportable;
use App\Services\Bitrix\Facades\BitrixHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RobotBitrixTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitrix:robot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для автоматического обновления токенов для доступа в Bitrix24';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $logger = Log::channel('bitrix');
        $logger->info('start tokens update');

        $tokens_to_update = BitrixToken::with('bitrixCode.integration.projects')->get();

        if (empty($tokens_to_update)) {
            $this->info('[Bitrix24] Нет токенов для обновления');
            $logger->info("finish. no tokens to update");
            return 0;
        }

        //$reportableData = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($tokens_to_update as $token) {
            try {
                BitrixHelper::requestFreshToken($token->bitrixCode);
                $successCount++;
            } catch (\Throwable $t) {
                $projects = $token->bitrixCode->integration->projects;
                $projects->each(function ($project) use ($token) {
                    $project->update(['integration_status' => false]);
                    //if ($project->pm) {
                    //    $project->pm->notify(
                    //        new BitrixConnectionErrorNotification($token, $project->name)
                    //    );
                    //}
                });
                //$reportableData[] = new BitrixReportable($token->bitrixCode->domain, $exception->getMessage());
                //echo "[{$token->bitrixCode->domain}] {$exception->getMessage()}" . PHP_EOL;
                //report($exception);

                $logger->error("update token error for domain {$token->bitrixCode->domain}: " . $t->getMessage());
                $errorCount++;
            }
        }

        if ($errorCount) {
            LogEvent::create(
                LogEvent::TYPE_ERROR_IMPORT,
                "Ошибки при обновлении токенов BitrixCrm-проектов. Успешно/ошибочно: $successCount/$errorCount. Подробности в логах.",
            );
        }

        $logger->info("finish tokens update. success/error: $successCount/$errorCount");
        return 0;

        /*
        $message = '[Bitrix24] Обновление токенов завершилось успешно';
        if (count($reportableData)) {
            $message = '[Bitrix24] Обновление токенов завершилось с ошибками';
            NotificationReports::bitrixReportToAdmins($reportableData, $message);
            $this->error($message);
            return 1;
        }

        NotificationReports::bitrixReportToSysAdmin([], $message);
        $this->info($message);

        return 0;
        */
    }
}
