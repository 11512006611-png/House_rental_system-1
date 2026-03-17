<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;

class HousePolicy
{
    public function update(User $user, House $house): bool
    {
        return $user->id === $house->owner_id || $user->isAdmin();
    }

    public function delete(User $user, House $house): bool
    {
        return $user->id === $house->owner_id || $user->isAdmin();
    }
}
