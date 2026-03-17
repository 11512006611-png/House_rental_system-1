<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OwnerNetPaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $propertyTitle,
        private readonly float $netAmount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'owner_net_payment_received',
            'title' => 'Net Payment Received',
            'message' => 'Net payment of Nu. ' . number_format($this->netAmount, 2) . ' received for ' . $this->propertyTitle . ' after commission deduction.',
        ];
    }
}
