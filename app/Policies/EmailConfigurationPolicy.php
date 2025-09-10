<?php

namespace App\Policies;

use App\Models\EmailConfiguration;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmailConfigurationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->hasRole(['Owner', 'Admin']);
    }
}