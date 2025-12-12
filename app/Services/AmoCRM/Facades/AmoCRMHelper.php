<?php

namespace App\Services\AmoCRM\Facades;
use App\Models\AmoCode;
use App\Services\AmoCRM\AmoCRMResponse;
use App\Services\AmoCRM\Exceptions\AmoCRMException;
use Illuminate\Support\Facades\Facade;

/**
 * @throws AmoCRMException
 * @method static AmoCRMResponse requestToken(AmoCode $amoCode)
 * @method static AmoCRMResponse requestFreshToken(AmoCode $amoCode)
 * @method static AmoCRMResponse runRequestCalls(AmoCode $amoCode, array $params, ?int $scheduleId)
 * @method static AmoCRMResponse saveCalls(AmoCode $amoCode, array $data)
 * @mixin \App\Services\AmoCRM\Helpers\AmoCRMHelper
 **/


class AmoCRMHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'amo_helper';
    }
}
