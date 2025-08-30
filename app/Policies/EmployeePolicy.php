<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    /**
     * Determine whether the user can view the model.
     * This rule checks if the employee's business ID matches the user's business ID.
     */
    public function view(User $user, Employee $employee): bool
    {
        return $user->business_id === $employee->business_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee $employee): bool
    {
        return $user->business_id === $employee->business_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->business_id === $employee->business_id;
    }
}