<?php

namespace App\Repositories\Facades;

use Illuminate\Support\Facades\Facade;

class ProjectRepository extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'repository.project';
    }
}