<?php

namespace App\Policies;

use App\Models\Designation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DesignationPolicy
{
    /**
     * Determine whether the user can view, update, or delete the model.
     */
    private function belongsToBusiness(User $user, Designation $designation): bool
    {
        return $user->business_id === $designation->business_id;
    }

    public function view(User $user, Designation $designation): bool
    {
        return $this->belongsToBusiness($user, $designation);
    }

    public function update(User $user, Designation $designation): bool
    {
        return $this->belongsToBusiness($user, $designation);
    }

    public function delete(User $user, Designation $designation): bool
    {
        return $this->belongsToBusiness($user, $designation);
    }
}