<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminCommissionReceived extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $propertyTitle,
        private readonly float $commissionAmount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commission_received',
            'title' => 'Commission Received',
            'message' => 'Commission of Nu. ' . number_format($this->commissionAmount, 2) . ' received for ' . $this->propertyTitle . '.',
        ];
    }
}
