<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionRequestRescheduled extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $propertyTitle,
        private readonly string $scheduleText,
        private readonly string $adminMessage
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inspection Rescheduled')
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('Your inspection request for ' . $this->propertyTitle . ' has been rescheduled.')
            ->line('New schedule: ' . $this->scheduleText)
            ->line('Admin message: ' . $this->adminMessage);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inspection_rescheduled',
            'title' => 'Inspection Rescheduled',
            'message' => 'Your inspection request for ' . $this->propertyTitle . ' has been rescheduled to ' . $this->scheduleText . '.',
            'scheduled_at' => $this->scheduleText,
            'admin_message' => $this->adminMessage,
        ];
    }
}
