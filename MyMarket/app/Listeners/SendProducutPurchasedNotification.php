<?php

namespace App\Listeners;

use App\Events;
use App\Events\ProductPurchased;
use App\Notifications\ProductPurchaseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendProducutPurchasedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(ProductPurchased $event): void
    {
        $event->admin->notify(new ProductPurchaseNotification());
    }
}
