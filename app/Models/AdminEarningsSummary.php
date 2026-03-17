<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminEarningsSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_commission_earned',
        'last_transaction_at',
    ];

    protected $casts = [
        'total_commission_earned' => 'decimal:2',
        'last_transaction_at' => 'datetime',
    ];
}
