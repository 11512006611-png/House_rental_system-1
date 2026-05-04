<?php

namespace App\Notifications;

use App\Models\MonthlySettlement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OwnerSettlementTransferred extends Notification implements ShouldQueue
{
    use Queueable;

    protected $settlement;

    /**
     * Create a new notification instance.
     */
    public function __construct(MonthlySettlement $settlement)
    {
        $this->settlement = $settlement;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $settlementMonth = \Carbon\Carbon::parse($this->settlement->settlement_month . '-01')->format('F Y');

        return (new MailMessage)
                    ->subject("Monthly Settlement Transferred - {$settlementMonth}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Your monthly settlement for {$settlementMonth} has been processed and transferred to your account.")
                    ->line("**Settlement Details:**")
                    ->line("• Total Rent Collected: Nu " . number_format($this->settlement->total_rent_collected, 2))
                    ->line("• Commission Deducted (" . number_format($this->settlement->commission_rate, 1) . "%): Nu " . number_format($this->settlement->commission_amount, 2))
                    ->line("• Net Amount Transferred: Nu " . number_format($this->settlement->net_amount, 2))
                    ->line("• Transfer Date: " . \Carbon\Carbon::parse($this->settlement->transferred_at)->format('M d, Y H:i'))
                    ->action('View Earnings', route('owner.earnings'))
                    ->line('Thank you for using our platform!')
                    ->salutation('Best regards, House Rental System Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Monthly Settlement Transferred',
            'message' => "Your settlement for " . \Carbon\Carbon::parse($this->settlement->settlement_month . '-01')->format('F Y') . " has been transferred. Net amount: Nu " . number_format($this->settlement->net_amount, 2),
            'settlement_id' => $this->settlement->id,
            'amount' => $this->settlement->net_amount,
            'month' => $this->settlement->settlement_month,
            'type' => 'settlement_transferred',
        ];
    }
}
            //
        ];
    }
}
