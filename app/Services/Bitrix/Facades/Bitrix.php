<?php

namespace App\Services\Bitrix\Facades;
use App\Services\Bitrix\BitrixResponse;
use App\Services\Bitrix\Exceptions\BitrixException;
use Illuminate\Support\Facades\Facade;

/**
 * @throws BitrixException
 * @method static BitrixResponse call(array $params)
 * @method static BitrixResponse callBatch(array $params)
 **/


class Bitrix extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'bitrix';
    }
}
