<?php

namespace App\Repositories\Facades;

use Illuminate\Support\Facades\Facade;

class AmoRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'repository.amo';
    }
}