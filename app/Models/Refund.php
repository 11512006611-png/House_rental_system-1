<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'move_out_request_id',
        'house_id',
        'tenant_id',
        'processed_by_admin_id',
        'security_deposit_amount',
        'damage_cost',
        'pending_dues',
        'refund_amount',
        'status',
        'inspection_notes',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'security_deposit_amount' => 'decimal:2',
        'damage_cost' => 'decimal:2',
        'pending_dues' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function moveOutRequest()
    {
        return $this->belongsTo(MoveOutRequest::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function processedByAdmin()
    {
        return $this->belongsTo(User::class, 'processed_by_admin_id');
    }

    public function calculateRefundAmount(): float
    {
        return max(0, round(((float) $this->security_deposit_amount) - ((float) $this->damage_cost) - ((float) $this->pending_dues), 2));
    }
}
