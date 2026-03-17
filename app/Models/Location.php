<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['dzongkhag_name', 'slug'];

    public function houses()
    {
        return $this->hasMany(House::class);
    }
}
