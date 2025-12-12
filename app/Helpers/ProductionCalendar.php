<?php

namespace App\Helpers;

use App\Models\Holiday;
use Carbon\Carbon;

class ProductionCalendar
{
    public static function make()
    {
        try {
            $calendar = simplexml_load_file('http://xmlcalendar.ru/data/ru/'.date('Y').'/calendar.xml');
            $calendar = $calendar->days->day;
        } catch (\Exception $exception) {
            report($exception);
        }

        foreach($calendar as $day ){
            $d = (array)$day->attributes()->d;
            $d = $d[0];
            $d = substr($d, 3, 2).'.'.substr($d, 0, 2).'.'.date('Y');
            if( $day->attributes()->t == 1 ) {
                $date = new Carbon($d);
                if (!Holiday::where('holiday_date', $date->format('Y-m-d'))->exists()) {
                    $newHoliday = new Holiday;
                    $newHoliday->holiday_date = $date->format('Y-m-d');
                    $newHoliday->save();
                }
            }
        }
    }

    public static function isHoliday($date): bool
    {
        $targetDate = new Carbon($date);
        return Holiday::where('holiday_date', $targetDate->format('Y-m-d'))->exists();
    }
}
