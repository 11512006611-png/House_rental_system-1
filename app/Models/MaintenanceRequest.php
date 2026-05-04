<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'house_id',
        'rental_id',
        'tenant_id',
        'owner_id',
        'category',
        'priority',
        'description',
        'preferred_visit_date',
        'status',
        'owner_response',
        'resolved_at',
        'needs_inspection',
        'payment_responsibility',
        'admin_notes',
        'inspection_notes',
        'service_provider_assigned_at',
        'approved_for_repair_at',
        'under_repair_at',
    ];

    protected $casts = [
        'preferred_visit_date' => 'date',
        'resolved_at' => 'datetime',
        'service_provider_assigned_at' => 'datetime',
        'approved_for_repair_at' => 'datetime',
        'under_repair_at' => 'datetime',
        'needs_inspection' => 'boolean',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
