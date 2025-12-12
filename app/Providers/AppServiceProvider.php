<?php

namespace App\Providers;

use App\Helpers\LoadIntegrationTypes;
use App\Services\SMSRU\SMSRUClient;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\Telegram\TelegramServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.force_https')) {
            $this->app['request']->server->set('HTTPS', true);
        }

        $this->app->register(RepositoryServiceProvider::class);
        $this->app->register(TelegramServiceProvider::class);

        $this->app->singleton('integration.types', function ($app) {
            return new LoadIntegrationTypes();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @param UrlGenerator $url
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        if (config('app.force_https')) {
            $url->formatScheme('https');
            URL::forceScheme('https');
        }

        $this->app->bind('sms_ru', function ($app) {
            return new SMSRUClient([
                'api_id' => config('services.smsru.api_id'),
            ]);
        });

        Queue::exceptionOccurred(function (JobExceptionOccurred $event) {
            report($event->exception);
        });
    }
}
