<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionRequestConfirmed extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $propertyTitle,
        private readonly string $scheduleText
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inspection Confirmed')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('Your inspection request for ' . $this->propertyTitle . ' has been confirmed.')
            ->line('Confirmed schedule: ' . $this->scheduleText)
            ->line('Please be available at the confirmed time.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inspection_confirmed',
            'title' => 'Inspection Confirmed',
            'message' => 'Your inspection request for ' . $this->propertyTitle . ' has been confirmed for ' . $this->scheduleText . '.',
            'scheduled_at' => $this->scheduleText,
        ];
    }
}
