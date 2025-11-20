<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveTypePolicy
{
    /**
     * Determine whether the user can view, update, or delete the model.
     */
    private function belongsToBusiness(User $user, LeaveType $leaveType): bool
    {
        return $user->business_id === $leaveType->business_id;
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        return $this->belongsToBusiness($user, $leaveType);
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $this->belongsToBusiness($user, $leaveType);
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $this->belongsToBusiness($user, $leaveType);
    }
}