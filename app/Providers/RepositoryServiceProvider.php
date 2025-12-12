<?php

namespace App\Providers;

use App\Repositories\AmoRepository;
use App\Repositories\BitrixRepository;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, BaseRepository::class);
        $this->app->bind(BitrixRepository::class);
        $this->app->bind(ProjectRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('repository.amo', function ($app) {
            return new AmoRepository();
        });
        $this->app->bind('repository.bitrix', function ($app) {
            return new BitrixRepository();
        });
        $this->app->bind('repository.project', function ($app) {
            return new ProjectRepository();
        });
        $this->app->bind('repository.task', function ($app) {
            return new TaskRepository();
        });
    }
}
