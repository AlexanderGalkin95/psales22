<?php

namespace App\Providers;

use App\Events\ProjectCreatedEvent;
use App\Events\ProjectEditedEvent;
use App\Events\TelegramBotEvent;
use App\Listeners\TelegramMainListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        TelegramBotEvent::class => [
            TelegramMainListener::class
        ],

        ProjectCreatedEvent::class => [
            TelegramMainListener::class
        ],

        ProjectEditedEvent::class => [
            TelegramMainListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
