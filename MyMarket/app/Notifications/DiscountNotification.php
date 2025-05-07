<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscountNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $ids;
    public $name;
    public $discount;

    /**
     * Create a new notification instance.
     *
     * @param array $ids
     * @param string $name
     * @param float $discount
     */
    public function __construct(array $ids, string $name, float $discount)
    {
        $this->ids = $ids;
        $this->name = $name;
        $this->discount = $discount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hello!')
            ->line("We are excited to offer a special discount {$this->name}.")
            ->line("You have received a discount of {$this->discount}% on the following products: " . implode(', ', $this->ids))
            ->action('View Discounted Products', url('/api/discountproducts'))
            ->line('Thank you for being a valued customer!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ids' => $this->ids,
            'name' => $this->name,
            'discount' => $this->discount,
        ];
    }
}
