<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'tenant_id',
        'rental_date',
        'end_date',
        'monthly_rent',
        'status',
        'lease_status',
        'lease_requested_at',
        'lease_reviewed_at',
        'notes',
    ];

    protected $casts = [
        'rental_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'lease_requested_at' => 'datetime',
        'lease_reviewed_at' => 'datetime',
    ];

    public function requestStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'active' => 'Accepted',
            'cancelled' => 'Rejected',
            'expired' => 'Expired',
            default => ucfirst((string) $this->status),
        };
    }

    public function paymentStatusLabel(): string
    {
        $latestPayment = $this->payments()->latest('payment_date')->first();

        if (! $latestPayment) {
            return 'Not paid';
        }

        return ucfirst((string) $latestPayment->status);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function leaseAgreement()
    {
        return $this->hasOne(LeaseAgreement::class);
    }

    public function moveOutRequests()
    {
        return $this->hasMany(MoveOutRequest::class);
    }
}
