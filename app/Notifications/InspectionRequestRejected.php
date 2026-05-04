<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionRequestRejected extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $propertyTitle,
        private readonly string $reason
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inspection Request Rejected')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('Your inspection request for ' . $this->propertyTitle . ' has been rejected.')
            ->line('Reason: ' . $this->reason);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inspection_rejected',
            'title' => 'Inspection Rejected',
            'message' => 'Your inspection request for ' . $this->propertyTitle . ' was rejected. Reason: ' . $this->reason,
            'rejection_reason' => $this->reason,
        ];
    }
}
