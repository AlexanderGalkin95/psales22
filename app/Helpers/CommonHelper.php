<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class CommonHelper
{
    protected const ESCAPED_SYMBOLS = ['_', '*', '[', ']', '>', '`', '(', ')', '~', '#', '+', '-', '=', '|', '{', '}', '.', '!'];

    public static function getNumberFromLetters(?string $input): int
    {
        if (empty($input)) return 0;
        $base = 26;
        $alfa = static::alfa();
        $length = count(str_split($input));

        return array_reduce(str_split($input), function ($carry, $item) use ($base, $alfa, $length, &$i) {
            $carry += $alfa[$item] * pow($base, $length - $i - 1);
            $i++;

            return $carry;
        }, 0);
    }

    private static function alfa(): array
    {
        return [
            'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9,
            'J' => 10, 'K' => 11, 'L' => 12, 'M' => 13, 'N' => 14, 'O' => 15, 'P' => 16, 'Q' => 17,
            'R' => 18, 'S' => 19, 'T' => 20, 'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25,
            'Z' => 26,
        ];
    }

    public static function numberToAlpha($n) {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }
        return $r;
    }


    public static function getMonth($date): int
    {
        return static::carbon($date)->month;
    }

    public static function getDay($date): int
    {
        return static::carbon($date)->day;
    }

    public static function getWeek($date): int
    {
        return static::carbon($date)->week;
    }

    public static function carbon($date): Carbon
    {
        return Carbon::parse($date);
    }

    public static function getTimeStamp($date): int
    {
        return Carbon::parse($date)->getTimestamp();
    }

    public static function getBackground($value)
    {
        if (is_numeric($value)) {
            return $value < 80 ? '#f7caca' : ($value < 90 ? '#fde0b5' : '#cbe2c6');
        }
        return '#ffffff';
    }

    public static function telegramEscape(string $str)
    {
        $replacement = array_map(fn ($it) => "\\$it", static::ESCAPED_SYMBOLS);
        return str_replace(static::ESCAPED_SYMBOLS, $replacement, "$str");
    }
}
