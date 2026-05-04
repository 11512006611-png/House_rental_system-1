<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'rental_id',
        'booking_id',
        'amount',
        'rent_amount',
        'security_deposit_amount',
        'service_fee_rate',
        'service_fee_amount',
        'total_advance_amount',
        'commission_rate',
        'commission_amount',
        'owner_share_amount',
        'payment_date',
        'billing_month',
        'payment_method',
        'payment_type',
        'held_by_admin',
        'transaction_id',
        'payment_proof_path',
        'status',
        'verification_status',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'rent_amount'       => 'decimal:2',
        'security_deposit_amount' => 'decimal:2',
        'service_fee_rate'  => 'decimal:2',
        'service_fee_amount' => 'decimal:2',
        'total_advance_amount' => 'decimal:2',
        'commission_rate'   => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'owner_share_amount' => 'decimal:2',
        'held_by_admin'     => 'boolean',
        'payment_date'      => 'date',
        'billing_month'     => 'date',
        'verified_at'       => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function commissionTransaction()
    {
        return $this->hasOne(AdminCommissionTransaction::class);
    }

    public function paymentTypeLabel(): string
    {
        return match ($this->payment_type) {
            'first_month_rent' => 'Advance Payment',
            'monthly_rent' => 'Monthly Rent',
            'security_deposit' => 'Security Deposit',
            'refund' => 'Refund',
            default => ucfirst((string) $this->payment_type),
        };
    }

    public function billingMonthLabel(): string
    {
        $date = $this->billing_month ?? $this->payment_date;

        return $date ? $date->format('M Y') : '—';
    }

    public static function calculateAdvanceBreakdown(float $rentAmount, float $securityDepositAmount, float $serviceFeeRate): array
    {
        $normalizedRent = max(0, round($rentAmount, 2));
        $normalizedDeposit = max(0, round($securityDepositAmount, 2));
        $normalizedRate = max(0, round($serviceFeeRate, 2));

        $serviceFeeAmount = round($normalizedRent * ($normalizedRate / 100), 2);
        $rentPayableAmount = round($normalizedRent + $serviceFeeAmount, 2);
        $ownerShareAmount = round($normalizedRent - $serviceFeeAmount, 2);
        $totalAdvanceAmount = round($rentPayableAmount + $normalizedDeposit, 2);

        return [
            'rent_amount' => $normalizedRent,
            'security_deposit_amount' => $normalizedDeposit,
            'service_fee_rate' => $normalizedRate,
            'service_fee_amount' => $serviceFeeAmount,
            'rent_payable_amount' => $rentPayableAmount,
            'owner_share_amount' => $ownerShareAmount,
            'total_advance_amount' => $totalAdvanceAmount,
        ];
    }

    public function isTrustMoney(): bool
    {
        return in_array($this->payment_type, ['security_deposit', 'refund'], true);
    }
}
