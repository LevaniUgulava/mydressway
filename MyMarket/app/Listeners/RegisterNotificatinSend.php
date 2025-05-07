<?php

namespace App\Listeners;

use App\Events\RegisterNotification;
use App\Notifications\RegisterNotification as NotificationsRegisterNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RegisterNotificatinSend
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RegisterNotification $event): void
    {
        $event->user->notify(new NotificationsRegisterNotification());
    }
}
