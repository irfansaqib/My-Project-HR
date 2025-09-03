<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions for all modules
        $modules = [
            'user', 'role', 'employee', 'department', 'designation',
            'salary-component', 'tax-rate', 'leave-application', 'leave-type',
            'client-login-credential', 'payroll', 'business'
        ];
        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$module}-{$action}"]);
            }
        }
        
        // Custom permissions
        Permission::firstOrCreate(['name' => 'salary-sheet-generate']);
        Permission::firstOrCreate(['name' => 'salary-sheet-view']);
        Permission::firstOrCreate(['name' => 'salary-sheet-delete']);
        Permission::firstOrCreate(['name' => 'payslip-view']);
        Permission::firstOrCreate(['name' => 'leave-application-approve']);
        
        // --- ADDED PRINT PERMISSIONS ---
        Permission::firstOrCreate(['name' => 'employee-print']);
        Permission::firstOrCreate(['name' => 'employee-print-contract']);
        Permission::firstOrCreate(['name' => 'salary-sheet-print']);

        // Create Roles and assign permissions
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        $allPermissions = Permission::all();
        $ownerRole->syncPermissions($allPermissions);
        $adminRole->syncPermissions($allPermissions);
        $userRole->syncPermissions(['employee-view', 'payslip-view', 'leave-application-create']);
        
        $user = User::first();
        if ($user && !$user->hasRole('Owner')) {
            $user->assignRole('Owner');
        }
    }
}