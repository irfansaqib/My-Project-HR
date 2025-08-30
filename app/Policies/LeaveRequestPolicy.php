<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveRequestPolicy
{
    /**
     * The "before" method automatically approves Owners and Admins for all actions.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole(['Owner', 'Admin'])) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can create leave requests.
     */
    public function create(User $user): bool
    {
        // Any user who is linked to an employee can apply for leave.
        return !is_null($user->employee_id);
    }

    /**
     * Determine whether the user can update their own leave request.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // An employee can only update their own request if it's still pending.
        return $user->employee_id === $leaveRequest->employee_id && $leaveRequest->status === 'pending';
    }

    /**
     * Determine whether the user can delete their own leave request.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->update($user, $leaveRequest); // Same rule as updating
    }
}