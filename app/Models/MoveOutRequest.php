<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoveOutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'tenant_id',
        'owner_id',
        'house_id',
        'reason',
        'move_out_date',
        'status',
        'owner_note',
        'reviewed_at',
        'completed_at',
    ];

    protected $casts = [
        'move_out_date' => 'date',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

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

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }
}
