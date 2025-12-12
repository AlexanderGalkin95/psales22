<?php

namespace App\Models;

use App\Casts\ManagerJsonToArray;
use App\Casts\PeriodJsonToArray;
use App\Helpers\ProductionCalendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

/**
 * @property integer id
 * @property integer telegram_bot_id
 * @property string name
 * @property string google_spreadsheet_id
 * @property string telegram
 * @property string whatsapp
 *
 * @property string timezone
 *
 * @property string report_time
 * @property string override_report_sent_at
 *
 * @property array managers
 * @property array period
 * @property array sending_period
 *
 * @property boolean include_holidays
 * @property boolean sending_include_holidays
 * @property boolean is_active
 *
 * @property \Illuminate\Support\Carbon created_at
 * @property \Illuminate\Support\Carbon updated_at
 *
 * @method static create(array $all)
 * @method static find(int $projectId)
 */
class GoogleProject extends Model
{
    use HasFactory;
    use Notifiable;

    protected $guarded = [];


    protected $casts = [
        'managers' => ManagerJsonToArray::class,
        'period' => PeriodJsonToArray::class,
        'sending_period' => PeriodJsonToArray::class,
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function telegram_channel(): BelongsTo
    {
        return $this->belongsTo(TelegramBot::class, 'telegram_bot_id');
    }

    public function routeNotificationForTelegram(): ?int
    {
        return (int)$this->telegram_channel()->value('chat_id');
    }

    public function checkTelegram()
    {
        if (!$this->telegram_channel) {
            $telegram = TelegramBot::where('username', $this->telegram)->first();
            if ($telegram) {
                $telegram->update([
                    'project_id' => $this->id,
                ]);
            }
            $this->load('telegram_channel');
        }
        return !!$this->telegram_channel;
    }

    public function getReportDate()
    {
        $date = now($this->timezone);
        $currentDay = $date->dayOfWeek;
        $period = $this->period;
        $firstDay = $period[0];
        $lastDay = end($period);
        if ($firstDay === $lastDay) {
            return $date->subDays(7);
        }
        if ($date->dayOfWeek === $firstDay) {
            return $date->subDays(7 - $lastDay + $firstDay);
        }
        $index = array_search($currentDay, $period);
        return $date->subDays($currentDay - $period[$index - 1]);
    }

    public function getReportDates($isManual = false): array
    {
        $dates = [];
        $currentDate = Carbon::now($this->timezone);
        $endDate = $currentDate->copy()->subDay()->startOfDay();
        $period = $this->period;

        // Логика для определения $startDate в зависимости от режима и наличия записи в БД
        $botReport = TelegramBotReport::where('project_id', $this->id)
            ->whereNotNull('last_report_sent_at')
            ->orderBy('last_report_sent_at', 'DESC')
            ->first();

        if ($this->override_report_sent_at) {
            $lastReportDate = Carbon::parse($this->override_report_sent_at, $this->timezone)->startOfDay();
        } else {
            $lastReportDate = Carbon::parse($botReport->last_report_sent_at, $this->timezone)->startOfDay();
        }
        $reportDateLimit = $currentDate->clone()->subDay(14)->startOfDay();
        $startDate = $reportDateLimit->greaterThan($lastReportDate) ? $reportDateLimit : $lastReportDate;
        // мин.дата отчета д.б. именно равна дате последней отправки, т.к. последняя отправка отправляла отчет за предыдущий день


        // "ручной режим" отключен - он теперь работает также, как и автоматический - ориентируется на последнее время отправки и ограничен двумя неделями
        if ($isManual || $botReport === null) {
//            $daysBeforeCurrent = array_filter($period, function ($day) use ($currentDate) {
//                return $day < $currentDate->format('N');
//            });
//
//            if (!empty($daysBeforeCurrent)) {
//                $startDay = max($daysBeforeCurrent); // Это дает нам "последний" день перед текущим днем
//            } else {
//                $startDay = max($period); // Это дает нам последний доступный рабочий день из $this->period
//            }
//
//            // Найдем разницу в днях между текущим днем и выбранным "предыдущим" днем
//            $daysDifference = $currentDate->format('N') - $startDay;
//
//            if ($daysDifference > 0) {
//                $startDate = (clone $currentDate)->modify('-' . $daysDifference . ' days');
//            } else {
//                $startDate = (clone $currentDate)->modify('-' . (7 + $daysDifference) . ' days');
//            }
//            $dates[] = $startDate->copy();
//            return $dates;

            for ($i = 0; $i < 7; $i++) {
                if (in_array($startDate->dayOfWeekIso, (array)$this->period)) {
                    $dates[] = $startDate->clone();
                    break;
                }
                $startDate->addDay();
            }
            return $dates;
        }

//        $maxStartDate = $currentDate->copy()->subDay(14)->startOfDay();
//        if ($this->override_report_sent_at) {
//            $lastReportDate = Carbon::parse($this->override_report_sent_at, $this->timezone)->startOfDay();
//        } else {
//            $lastReportDate = Carbon::parse($botReport->last_report_sent_at, $this->timezone)->startOfDay();
//        }
//        $startDate = max($lastReportDate, $maxStartDate);


        // Цикл для сбора всех подходящих дат в массив $dates
        while ($startDate <= $endDate) {
            if (in_array($startDate->dayOfWeekIso, $this->period)) {
                if (!$this->include_holidays && ProductionCalendar::isHoliday($startDate)) {
                    $startDate->addDay();
                    continue;
                }
                $dates[] = $startDate->clone();
            }
            $startDate->addDay();
        }

        return $dates;
    }

}
