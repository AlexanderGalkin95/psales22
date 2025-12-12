<?php

namespace App\Console\Commands;

use App\Events\ProjectEditedEvent;
use App\Exceptions\RequestAmoTokenException;
use App\Models\AmoCode;
use App\Services\AmoCRM\Exceptions\AmoCRMException;
use App\Services\AmoCRM\Facades\AmoCRMHelper;
use Exception;
use Illuminate\Console\Command;

class RequestAmoToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amo:token {amo_code_id : id кода авторизации в AmoCRM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запрос нового токена для авторизации в сервис AmoCRM';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $errors = 0;
        $amo_code_id = (int) $this->argument('amo_code_id');
        if (empty($amo_code_id))
            throw new RequestAmoTokenException("Идентификатор интеграции не может быть пустым!", 422);

        $amoCode = AmoCode::find($amo_code_id);
        if (empty($amoCode))
            throw new RequestAmoTokenException("Интеграция с ID = '$amo_code_id' не найдена!", 422);

        try {
            AmoCRMHelper::requestToken($amoCode);

            echo "[{$amoCode->domain}] Токен установлен успешно!" . PHP_EOL;

            $projects = $amoCode->integration->projects;
            if ($projects->count()) {
                $projects->each(function ($project) {
                    event(new ProjectEditedEvent($project->id));
                });
            }

        } catch (AmoCRMException $e) {
            $errors = 1;
            report($e);

            echo "[{$amoCode->domain}] {$e->getMessage()}" . PHP_EOL;
        }

        if ($errors === 1) {
            return 1;
        }

        return 0;
    }
}
