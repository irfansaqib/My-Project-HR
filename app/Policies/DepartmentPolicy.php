<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view, update, or delete the model.
     */
    private function belongsToBusiness(User $user, Department $department): bool
    {
        return $user->business_id === $department->business_id;
    }

    public function view(User $user, Department $department): bool
    {
        return $this->belongsToBusiness($user, $department);
    }

    public function update(User $user, Department $department): bool
    {
        return $this->belongsToBusiness($user, $department);
    }

    public function delete(User $user, Department $department): bool
    {
        return $this->belongsToBusiness($user, $department);
    }
}