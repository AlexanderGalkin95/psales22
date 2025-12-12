<?php

namespace App\Console\Commands;

use App\Events\ProjectEditedEvent;
use App\Exceptions\RefreshAmoTokenException;
use App\Models\AmoCode;
use App\Services\AmoCRM\Exceptions\AmoCRMException;
use App\Services\AmoCRM\Facades\AmoCRMHelper;
use Exception;
use Illuminate\Console\Command;

class RefreshAmoToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:refresh_token {amo_code_id : id кода авторизации в AmoCRM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запрос обновления токена для авторизации в сервис AmoCRM';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $amo_code_id = (int) $this->argument('amo_code_id');
        if (empty($amo_code_id))
            throw new RefreshAmoTokenException("Идентификатор виджета не может быть пустым", 422);

        $amoCode = AmoCode::find($amo_code_id);

        if (empty($amoCode))
            throw new RefreshAmoTokenException("Интеграция с идентификатором '$amo_code_id' не найдена!", 422);

        if (empty($amoCode->amoToken)) {
            throw new RefreshAmoTokenException("Для интеграции c доменом '{$amoCode->domain}' токен не найден!", 422);
        }
        try {
            AmoCRMHelper::requestFreshToken($amoCode);

            echo "[{$amoCode->domain}] Токен обновлен успешно!" . PHP_EOL;

            $projects = $amoCode->integration->projects;
            if ($projects->count()) {
                $projects->each(function ($project) {
                    event(new ProjectEditedEvent($project->id));
                });
            }
        } catch (AmoCRMException $e) {
            report($e);

            echo "[{$amoCode->domain}] {$e->getMessage()}" . PHP_EOL;
            return 1;
        }

        return 0;
    }
}
