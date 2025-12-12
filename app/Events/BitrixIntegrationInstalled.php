<?php

namespace App\Events;

use App\Services\Bitrix\Helpers\NotificationReports;
use App\Services\Bitrix\Notifications\BitrixReportable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BitrixIntegrationInstalled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($bitrixCode)
    {
        $message = "Запрос токена для домена [$bitrixCode->domain] завершился успешно";

        $reportableData = new BitrixReportable($bitrixCode->domain, '');

        NotificationReports::bitrixReportToAdmins([$reportableData], $message);
    }
}
