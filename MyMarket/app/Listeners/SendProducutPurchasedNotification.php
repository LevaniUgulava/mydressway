<?php

namespace App\Listeners;

use App\Events;
use App\Events\ProductPurchased;
use App\Notifications\ProductPurchaseNotification;
use App\Services\SendGridService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendProducutPurchasedNotification implements ShouldQueue
{
    protected $sendGridService;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->sendGridService = new SendGridService();
    }

    /**
     * Handle the event.
     */
    public function handle(ProductPurchased $event): void
    {
        $this->sendGridService->sendEmail(
            $event->data->to_email,
            $event->data->to_name,
            [
                'first_name' => $event->data->order_items['first_name'],
                'last_name' => $event->data->order_items['last_name'],
                'order_id' => $event->data->order_items['order_id'],
                'total' => $event->data->order_items['total'],
                'items' => $event->data->order_items['items'],
            ],
            $event->data->template_key
        );
    }
}
