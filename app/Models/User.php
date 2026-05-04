<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Payment;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'profile_image',
        'gender',
        'date_of_birth',
        'current_address',
        'status',
        'bank_name',
        'account_number',
        'account_holder_name',
        'advance_payment_amount',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    public function houses()
    {
        return $this->hasMany(House::class, 'owner_id');
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'tenant_id');
    }

    public function inspectionRequestsAsTenant()
    {
        return $this->hasMany(Inspection::class, 'tenant_id');
    }

    public function inspectionRequestsHandledByAdmin()
    {
        return $this->hasMany(Inspection::class, 'handled_by_admin_id');
    }

    public function bookingsAsTenant()
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    public function bookingsAsOwner()
    {
        return $this->hasMany(Booking::class, 'owner_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isUser(): bool
    {
        return $this->isTenant();
    }

    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function dashboardRoute(): string
    {
        if ($this->isAdmin()) {
            return 'admin.dashboard';
        }

        if ($this->isTenant()) {
            return 'tenant.dashboard';
        }

        if ($this->isOwner()) {
            return 'owner.dashboard';
        }

        return 'home';
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'tenant_id');
    }

    public function processedRefunds()
    {
        return $this->hasMany(Refund::class, 'processed_by_admin_id');
    }

    public function tenantReviews()
    {
        return $this->hasMany(TenantReview::class);
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->profile_image) {
            return null;
        }

        return Storage::url($this->profile_image);
    }
}
