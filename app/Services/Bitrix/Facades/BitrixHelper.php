<?php

namespace App\Services\Bitrix\Facades;

use App\Models\BitrixCode;
use App\Services\Bitrix\BitrixResponse;
use App\Services\Bitrix\Exceptions\BitrixException;
use Illuminate\Support\Facades\Facade;

/**
 * @throws BitrixException
 * @method static BitrixResponse requestFreshToken(BitrixCode $bitrixCode)
 * @method static BitrixResponse request(BitrixCode $bitrixCode, array $params)
 * @method static BitrixResponse batchRequest(BitrixCode $bitrixCode, array $params)
 * @method static BitrixResponse savePendingToken($data)
 * @method static BitrixResponse runRequestCalls(BitrixCode $bitrixCode, array $params, ?int $scheduleId)
 *
 **/


class BitrixHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bitrix_helper';
    }
}
