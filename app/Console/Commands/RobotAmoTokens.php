<?php

namespace App\Console\Commands;

use App\Models\AmoToken;
use App\Models\Logs\LogEvent;
use App\Notifications\AmoCRMConnectionErrorNotification;
use App\Services\AmoCRM\Facades\AmoCRMHelper;
use App\Services\AmoCRM\Helpers\NotificationReports;
use App\Services\AmoCRM\Notifications\AmoCRMReportable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RobotAmoTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:robot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для автоматического обновления токенов для доступа в AmoCRM';

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
        $logger = Log::channel('amo');
        $logger->info('start tokens update');

        $tokens_to_update = AmoToken::with('amoCode.integration.projects')->get();

        if (empty($tokens_to_update)) {
            $this->info('Нет токенов для обновления');
            $logger->info("finish. no tokens to update");
            return 0;
        }

        //$reportableData = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($tokens_to_update as $token) {
            try {
                AmoCRMHelper::requestFreshToken($token->amoCode);
                $successCount++;
            } catch (\Throwable $t) {
                $projects = $token->amoCode->integration->projects;
                $projects->each(function ($project) use ($token, &$projectsNames) {
                    $project->update(['integration_status' => false]);
                    //if ($project->pm) {
                    //    $project->pm->notify(
                    //        new AmoCRMConnectionErrorNotification($token, $project->name)
                    //    );
                    //}
                });
                //$reportableData[] = new AmoCRMReportable($token->amoCode->domain, $exception->getMessage());
                //echo "[{$token->amoCode->domain}] {$exception->getMessage()}" . PHP_EOL;
                //report($exception);

                $logger->error("update token error for domain {$token->amoCode->domain}: " . $t->getMessage());
                $errorCount++;
            }
        }

        if ($errorCount) {
            LogEvent::create(
                LogEvent::TYPE_ERROR_IMPORT,
                "Ошибки при обновлении токенов AmoCrm-проектов. Успешно/ошибочно: $successCount/$errorCount. Подробности в логах.",
            );
        }

        $logger->info("finish tokens update. success/error: $successCount/$errorCount");
        return 0;

        /*
        if (count($reportableData)) {
            $message = '[AmoCRM] Обновление токенов завершилось с ошибками';
            NotificationReports::amoCRMReportToAdmins($reportableData, $message);
            $this->error($message);
        } else {
            $message = '[AmoCRM] Обновление токенов завершилось успешно';
            NotificationReports::amoCRMReportToSysAdmin([], $message);
            $this->info($message);
        }

        return count($reportableData) ? 1 : 0;
        */
    }
}
