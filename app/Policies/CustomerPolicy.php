<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // Check if the customer's business ID matches the user's business ID
        return $user->business_id === $customer->business_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any user belonging to a business can create a customer
        return !is_null($user->business_id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        // Check if the customer's business ID matches the user's business ID
        return $user->business_id === $customer->business_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // Check if the customer's business ID matches the user's business ID
        return $user->business_id === $customer->business_id;
    }
}