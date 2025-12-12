<?php


namespace App\Services\Bitrix;

use App\Services\Bitrix\Helpers\BitrixHelper;
use Illuminate\Support\ServiceProvider;

class BitrixServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('bitrix', function () {
            return new Bitrix();
        });
        $this->app->bind('bitrix_helper', function () {
            return new BitrixHelper();
        });

    }
}
