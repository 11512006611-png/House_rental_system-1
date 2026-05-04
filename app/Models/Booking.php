<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'tenant_id',
        'owner_id',
        'rental_id',
        'monthly_rent',
        'first_month_rent_amount',
        'security_deposit_amount',
        'service_fee_rate',
        'service_fee_amount',
        'total_advance_amount',
        'status',
        'booking_date',
        'confirmed_at',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'first_month_rent_amount' => 'decimal:2',
        'security_deposit_amount' => 'decimal:2',
        'service_fee_rate' => 'decimal:2',
        'service_fee_amount' => 'decimal:2',
        'total_advance_amount' => 'decimal:2',
        'booking_date' => 'date',
        'confirmed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }
}
