<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCommissionTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'tenant_id',
        'owner_id',
        'property_id',
        'payment_amount',
        'admin_commission',
        'owner_share',
        'transaction_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'owner_share' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function property()
    {
        return $this->belongsTo(House::class, 'property_id');
    }
}
