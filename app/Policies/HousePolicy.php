<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;

class HousePolicy
{
    public function update(User $user, House $house): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id !== $house->owner_id) {
            return false;
        }

        $hasActiveRental = $house->rentals()->where('status', 'active')->exists();

        return ! $hasActiveRental;
    }

    public function delete(User $user, House $house): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id !== $house->owner_id) {
            return false;
        }

        if ($house->status === 'rented') {
            return false;
        }

        $hasActiveRental = $house->rentals()->where('status', 'active')->exists();

        return ! $hasActiveRental;
    }
}
