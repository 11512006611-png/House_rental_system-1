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
        'amount',
        'commission_rate',
        'commission_amount',
        'owner_share_amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'payment_proof_path',
        'status',
        'verification_status',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'commission_rate'   => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'owner_share_amount' => 'decimal:2',
        'payment_date'      => 'date',
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

    public function commissionTransaction()
    {
        return $this->hasOne(AdminCommissionTransaction::class);
    }
}
