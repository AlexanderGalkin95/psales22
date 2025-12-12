<?php


namespace App\Services\Bitrix\Helpers;


use App\Models\User;
use App\Notifications\BitrixReportsNotification;
use App\Services\Bitrix\Notifications\BitrixReportable;
use Illuminate\Support\Facades\Notification;

class NotificationReports
{
    /**
     *
     * @param BitrixReportable[] $reportableData
     * @param $message
     */
    public static function bitrixReportToAdmins(array $reportableData, $message)
    {
        User::admins()->each(function ($admin) use ($reportableData, $message) {
            $admin->notify(new BitrixReportsNotification($reportableData, $message));
        });

        static::bitrixReportToSysAdmin($reportableData, $message);
    }

    /**
     * @param BitrixReportable[] $reportableData
     * @param $message
     */
    public static function bitrixReportToTechnicalSupport(array $reportableData, $message)
    {
        User::technicalSupports()->each(function ($support) use ($reportableData, $message) {
            $support->notify(new BitrixReportsNotification($reportableData, $message));
        });
    }

    /**
     * Report to the Sys Admin of the system
     * @param BitrixReportable[] $reportableData
     * @param $message
     */
    public static function bitrixReportToSysAdmin(array $reportableData, $message)
    {
        Notification::route('mail', config('mail.sysadmin_email_address'))->notify(
            new BitrixReportsNotification($reportableData, $message)
        );
    }
}
