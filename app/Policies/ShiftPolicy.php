<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShiftPolicy
{
    /**
     * Determine whether the user can view, update, or delete the model.
     */
    private function belongsToBusiness(User $user, Shift $shift): bool
    {
        return $user->business_id === $shift->business_id;
    }

    public function view(User $user, Shift $shift): bool
    {
        return $this->belongsToBusiness($user, $shift);
    }

    public function update(User $user, Shift $shift): bool
    {
        return $this->belongsToBusiness($user, $shift);
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $this->belongsToBusiness($user, $shift);
    }
}