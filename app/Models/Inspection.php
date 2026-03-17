<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'house_id',
        'tenant_id',
        'preferred_date',
        'preferred_time',
        'message',
        'status',
        'owner_notes',
        'scheduled_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'scheduled_at'   => 'datetime',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Pending',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
            'completed' => 'Completed',
            default     => ucfirst((string) $this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'approved'  => 'success',
            'rejected'  => 'danger',
            'completed' => 'info',
            default     => 'secondary',
        };
    }
}
