<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BusinessPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Business $business): bool
    {
        // This rule allows the action if the logged-in user's ID
        // matches the user_id on the business record.
        return $user->id === $business->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Business $business): bool
    {
        // This uses the same logic for the update action.
        return $user->id === $business->user_id;
    }
}