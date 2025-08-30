<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $currentUser, User $userToUpdate): bool
    {
        // Users can only be updated if they belong to the same business.
        return $currentUser->business_id === $userToUpdate->business_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $currentUser, User $userToDelete): bool
    {
        // Prevent deleting the main Business Owner (who has role 'owner')
        if ($userToDelete->hasRole('Owner')) {
            return false;
        }

        // Prevent a user from deleting themselves
        if ($currentUser->id === $userToDelete->id) {
            return false;
        }

        // Otherwise, allow deletion if they are in the same business.
        return $currentUser->business_id === $userToDelete->business_id;
    }
}