<?php

namespace App\Events;

use App\Services\AmoCRM\Exceptions\AmoCRMException;
use App\Services\AmoCRM\Facades\AmoCRMHelper;
use App\Services\AmoCRM\Helpers\NotificationReports;
use App\Services\AmoCRM\Notifications\AmoCRMReportable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AmoCRMWidgetInstalled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($amoCode)
    {
        if ($amoCode->amoToken !== null) {
            $amoCode->amoToken->delete();
        }
        $this->requestToken($amoCode);
    }

    private function requestToken($amoCode)
    {
        try {
            AmoCRMHelper::requestToken($amoCode);
//            $amoCode->integration->projects->each(function ($project) {
//                event(new ProjectEditedEvent($project->id));
//            });
            logger("Запрос токена для домена [$amoCode->domain] завершился успешно");
        } catch (AmoCRMException $e) {
            logger("Запрос токена для домена [$amoCode->domain] завершился с ошибками: " . $e->getMessage());
        }

        //$reportableData = new AmoCRMReportable($amoCode->domain, $error);

        //NotificationReports::amoCRMReportToAdmins([$reportableData], $message);
    }
}
