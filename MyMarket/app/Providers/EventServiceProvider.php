<?php

namespace App\Providers;

use App\Events\ProductPurchased;
use App\Events\RegisterNotification;
use App\Events\Sitelog;
use App\Listeners\RegisterNotificatinSend;
use App\Listeners\SendProducutPurchasedNotification;
use App\Listeners\SitelogAction;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        RegisterNotification::class  => [
            RegisterNotificatinSend::class,
        ],
        ProductPurchased::class => [
            SendProducutPurchasedNotification::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void {}

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
