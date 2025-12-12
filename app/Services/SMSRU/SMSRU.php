<?php


namespace App\Services\SMSRU;


use Illuminate\Support\Facades\Facade;

/**
 * Class SMSRU
 * @package App\Services\SMSRU
 *
 * @method static send(Message $message)
 */

class SMSRU extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sms_ru';
    }
}
