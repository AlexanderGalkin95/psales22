<?php


namespace App\Services\AmoCRM;

use App\Services\AmoCRM\Helpers\AmoCRMHelper;
use Illuminate\Support\ServiceProvider;

class AmoCRMServiceProvider extends ServiceProvider
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
        $this->app->bind('amo', function () {
            return new AmoCRM();
        });
        $this->app->bind('amo_helper', function () {
            return new AmoCRMHelper();
        });

    }
}
