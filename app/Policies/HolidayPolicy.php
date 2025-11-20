<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HolidayPolicy
{
    /**
     * Determine whether the user can view, update, or delete the model.
     */
    private function belongsToBusiness(User $user, Holiday $holiday): bool
    {
        return $user->business_id === $holiday->business_id;
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $this->belongsToBusiness($user, $holiday);
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $this->belongsToBusiness($user, $holiday);
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $this->belongsToBusiness($user, $holiday);
    }
}