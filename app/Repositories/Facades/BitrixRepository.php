<?php

namespace App\Repositories\Facades;

use Illuminate\Support\Facades\Facade;

class BitrixRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'repository.bitrix';
    }
}