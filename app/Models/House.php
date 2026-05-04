<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'location_id',
        'inspected_by_admin_id',
        'title',
        'location',
        'type',
        'price',
        'security_deposit_amount',
        'description',
        'image',
        'bedrooms',
        'bathrooms',
        'area',
        'address',
        'status',
        'admin_commission_rate',
        'inspection_scheduled_at',
        'inspection_schedule_acknowledged_at',
        'inspected_at',
        'admin_inspection_notes',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'security_deposit_amount' => 'decimal:2',
        'admin_commission_rate' => 'decimal:2',
        'is_featured' => 'boolean',
        'inspection_scheduled_at' => 'datetime',
        'inspection_schedule_acknowledged_at' => 'datetime',
        'inspected_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function locationModel()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function inspectedByAdmin()
    {
        return $this->belongsTo(User::class, 'inspected_by_admin_id');
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function inspectionRequests()
    {
        return $this->hasMany(Inspection::class);
    }

    public function houseImages()
    {
        return $this->hasMany(HouseImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getImageUrlAttribute(): string
    {
        $firstGalleryImage = $this->relationLoaded('houseImages')
            ? $this->houseImages->first()
            : $this->houseImages()->first();

        if ($firstGalleryImage?->path) {
            return asset('storage/' . $firstGalleryImage->path);
        }

        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        // Deterministic placeholder using house ID for visual variety
        $seeds = [10, 20, 30, 40, 50, 60, 70, 80];
        $seed  = $seeds[($this->id ?? 1) % count($seeds)];
        return "https://picsum.photos/seed/house{$seed}/600/400";
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Nu. ' . number_format($this->price, 0);
    }
}
