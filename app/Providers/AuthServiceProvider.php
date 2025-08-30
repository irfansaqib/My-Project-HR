<?php

namespace App\Providers;

use App\Models\Business;
use App\Policies\BusinessPolicy;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\LeaveRequest;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        Business::class => BusinessPolicy::class,
        User::class => UserPolicy::class,
        LeaveRequest::class => LeaveRequestPolicy::class, // <-- Add this line
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole(['Owner', 'Admin']) ? true : null;
        });
    }
}