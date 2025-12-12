<?php


namespace App\Services\AmoCRM\Helpers;


use App\Models\User;
use App\Notifications\AmoCRMReportsNotification;
use App\Services\AmoCRM\Notifications\AmoCRMReportable;
use Illuminate\Support\Facades\Notification;

class NotificationReports
{
    /**
     *
     * @param AmoCRMReportable[] $reportableData
     * @param $message
     */
    public static function amoCRMReportToAdmins(array $reportableData, $message)
    {
        User::admins()->each(function ($admin) use ($reportableData, $message) {
            $admin->notify(new AmoCRMReportsNotification($reportableData, $message));
        });

        static::amoCRMReportToSysAdmin($reportableData, $message);
    }

    /**
     * @param AmoCRMReportable[] $reportableData
     * @param $message
     */
    public static function amoCRMReportToTechnicalSupport(array $reportableData, $message)
    {
        User::technicalSupports()->each(function ($support) use ($reportableData, $message) {
            $support->notify(new AmoCRMReportsNotification($reportableData, $message));
        });
    }

    /**
     * Report to the Sys Admin of the system
     * @param AmoCRMReportable[] $reportableData
     * @param $message
     */
    public static function amoCRMReportToSysAdmin(array $reportableData, $message)
    {
        Notification::route('mail', config('mail.sysadmin_email_address'))->notify(
            new AmoCRMReportsNotification($reportableData, $message)
        );
    }
}
