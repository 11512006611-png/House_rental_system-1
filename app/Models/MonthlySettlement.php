<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class MonthlySettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'settlement_month',
        'total_rent_collected',
        'commission_rate',
        'commission_amount',
        'final_amount',
        'net_amount',
        'status',
        'transferred_at',
        'transfer_notes',
        'transfer_proof_path',
        'owner_account_number',
        'processed_by',
        'payment_breakdown',
    ];

    protected $casts = [
        'settlement_month' => 'date',
        'total_rent_collected' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'transferred_at' => 'date',
        'payment_breakdown' => 'array',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Helper methods
    public function getSettlementMonthLabel(): string
    {
        return $this->settlement_month->format('F Y');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }

    public function calculateCommission(): float
    {
        return round($this->total_rent_collected * ($this->commission_rate / 100), 2);
    }

    public function calculateFinalAmount(): float
    {
        return $this->total_rent_collected - $this->commission_amount;
    }

    // Static methods for calculations
    public static function calculateMonthlySettlement(User|int $owner, Carbon|string $month): array
    {
        $ownerId = $owner instanceof User ? $owner->id : (int) $owner;
        $monthDate = $month instanceof Carbon ? $month->copy()->startOfMonth() : Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth();

        $payments = Payment::where('payment_type', 'monthly_rent')
            ->where('verification_status', 'verified')
            ->whereHas('rental.house', function ($query) use ($ownerId) {
                $query->where('owner_id', $ownerId);
            })
            ->with(['rental.house', 'rental.tenant'])
            ->get()
            ->filter(function (Payment $payment) use ($monthDate) {
                $billingMonth = $payment->billing_month?->copy()->startOfMonth();
                $paymentDate = $payment->payment_date?->copy()->startOfMonth();

                return ($billingMonth && $billingMonth->isSameMonth($monthDate))
                    || ($paymentDate && $paymentDate->isSameMonth($monthDate));
            })
            ->values();

        $totalRent = $payments->sum('amount');
        $ownerModel = $owner instanceof User ? $owner : User::find($ownerId);
        $commissionRate = $ownerModel?->houses()->first()?->admin_commission_rate ?? 10.00; // Default 10%
        $commissionAmount = round($totalRent * ($commissionRate / 100), 2);
        $finalAmount = $totalRent - $commissionAmount;
        $settlement = self::where('owner_id', $ownerId)
            ->whereDate('settlement_month', $monthDate)
            ->first();

        return [
            'settlement_id' => $settlement?->id,
            'settlement_status' => $settlement?->status ?? 'pending',
            'settlement_date' => $settlement?->processed_at,
            'total_rent_collected' => $totalRent,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'final_amount' => $finalAmount,
            'net_amount' => $finalAmount,
            'payment_count' => $payments->count(),
            'payments' => $payments,
            'payment_breakdown' => $payments->map(function (Payment $payment) {
                return [
                    'payment_id' => $payment->id,
                    'property' => $payment->rental?->house?->title,
                    'tenant' => $payment->rental?->tenant?->name,
                    'amount' => $payment->amount,
                    'type' => $payment->paymentTypeLabel(),
                    'month' => $payment->billingMonthLabel(),
                    'submitted_at' => $payment->created_at?->format('d M Y H:i'),
                ];
            })->all(),
        ];
    }
}
