<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Payment;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
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

    public function tenantReviews()
    {
        return $this->hasMany(TenantReview::class);
    }
}
