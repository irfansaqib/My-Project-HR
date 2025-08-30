<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Permissions for Employees
        Permission::create(['name' => 'employee-view']);
        Permission::create(['name' => 'employee-create']);
        Permission::create(['name' => 'employee-edit']);
        Permission::create(['name' => 'employee-delete']);

        // Define Permissions for Users
        Permission::create(['name' => 'user-view']);
        Permission::create(['name' => 'user-create']);
        Permission::create(['name' => 'user-edit']);
        Permission::create(['name' => 'user-delete']);
        
        // Define Permissions for Customers
        Permission::create(['name' => 'customer-view']);
        Permission::create(['name' => 'customer-create']);
        Permission::create(['name' => 'customer-edit']);
        Permission::create(['name' => 'customer-delete']);

        // Define Permissions for Leave Requests (Spelling Corrected)
        Permission::create(['name' => 'leave-request-view']);
        Permission::create(['name' => 'leave-request-create']);
        Permission::create(['name' => 'leave-request-edit']);
        Permission::create(['name' => 'leave-request-delete']);
        Permission::create(['name' => 'leave-request-approve']);

        Permission::create(['name' => 'reports-view']);

        // Define Roles
        $userRole = Role::create(['name' => 'User']);
        $adminRole = Role::create(['name' => 'Admin']);
        $ownerRole = Role::create(['name' => 'Owner']);
    }
}