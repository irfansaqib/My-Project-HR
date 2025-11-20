<?php

namespace App\Providers;

// Existing Models & Policies
use App\Models\Business;
use App\Policies\BusinessPolicy;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\LeaveRequest;
use App\Policies\LeaveRequestPolicy;
use App\Models\Customer;
use App\Policies\CustomerPolicy;

// ** 1. IMPORT THE 5 NEW MODELS **
use App\Models\Department;
use App\Models\Designation;
use App\Models\Holiday;
use App\Models\LeaveType;
use App\Models\Shift;

// ** 2. IMPORT THE 5 NEW POLICIES **
use App\Policies\DepartmentPolicy;
use App\Policies\DesignationPolicy;
use App\Policies\HolidayPolicy;
use App\Policies\LeaveTypePolicy;
use App\Policies\ShiftPolicy;

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
        LeaveRequest::class => LeaveRequestPolicy::class,
        Customer::class => CustomerPolicy::class,

        // ** 3. ADD THE 5 NEW MAPPINGS **
        Department::class => DepartmentPolicy::class,
        Designation::class => DesignationPolicy::class,
        Holiday::class => HolidayPolicy::class,
        LeaveType::class => LeaveTypePolicy::class,
        Shift::class => ShiftPolicy::class,
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