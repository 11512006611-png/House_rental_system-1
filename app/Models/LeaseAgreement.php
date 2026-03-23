<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaseAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_id',
        'rental_id',
        'owner_id',
        'tenant_id',
        'house_id',
        'file_path',
        'original_name',
        'monthly_rent',
        'deposit_amount',
        'payment_status',
        'lease_start_date',
        'lease_end_date',
        'tenant_signature_name',
        'tenant_signed_at',
        'owner_signature_name',
        'owner_signed_at',
        'uploaded_at',
        'generated_at',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'tenant_signed_at' => 'datetime',
        'owner_signed_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }
}
