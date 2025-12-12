<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoCallStatus extends Model
{
    use HasFactory;

    protected $table = 'ref_amo_call_statuses';

    const CALL_VOICE_MESSAGE = 1;
    const CALL_CALLBACK_LATER = 2;
    const CALL_NOT_AVAILABLE = 3;
    const CALL_SUCCESS = 4;
    const CALL_WRONG_NUMBER = 5;
    const CALL_UNREACHABLE = 6;
    const CALL_NUMBER_IS_BUSY = 7;
    const CALL_UNKNOWN = 8;

    protected static array $call_status_values = [
        'voice_message' => self::CALL_VOICE_MESSAGE,
        'call_back_later' => self::CALL_CALLBACK_LATER,
        'not_available' => self::CALL_NOT_AVAILABLE,
        'contact' => self::CALL_SUCCESS,
        'wrong_number' => self::CALL_WRONG_NUMBER,
        'unreachable' => self::CALL_UNREACHABLE,
        'number_is_busy' => self::CALL_NUMBER_IS_BUSY,
        'unknown' => self::CALL_UNKNOWN,
    ];

    /**
     * @return array
     */
    public static function getCallStatusValues(): array
    {
        return self::$call_status_values;
    }

    /**
     * @param string $system_name
     * @return int
     */
    public static function getCallStatusValue(string $system_name): int
    {
        return self::$call_status_values[$system_name] ?? self::CALL_UNKNOWN;
    }

}
