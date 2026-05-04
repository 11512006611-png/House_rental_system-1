<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $table = 'inspection_requests';

    protected $fillable = [
        'house_id',
        'tenant_id',
        'preferred_date',
        'preferred_time',
        'message',
        'status',
        'owner_notes',
        'scheduled_at',
        'admin_message',
        'rejection_reason',
        'handled_by_admin_id',
        'handled_at',
        'tenant_decision',
        'tenant_decision_message',
        'tenant_decision_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'scheduled_at'   => 'datetime',
        'handled_at'     => 'datetime',
        'tenant_decision_at' => 'datetime',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function handledByAdmin()
    {
        return $this->belongsTo(User::class, 'handled_by_admin_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'     => 'Pending',
            'confirmed'   => 'Confirmed',
            'completed'   => 'Completed',
            'rescheduled' => 'Rescheduled',
            'cancelled'   => 'Cancelled',
            'rejected'    => 'Rejected',
            default       => ucfirst((string) $this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'     => 'yellow',
            'confirmed'   => 'green',
            'completed'   => 'green',
            'rescheduled' => 'blue',
            'cancelled'   => 'gray',
            'rejected'    => 'red',
            default       => 'gray',
        };
    }
}
