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
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Standard Modules (View, Create, Edit, Delete)
        // I have updated the names here to ensure strict separation
        $modules = [
            // Admin
            'user', 'role', 'business', 'email-configuration',
            
            // HR
            'employee', 'department', 'designation', 'employee-exit', 'warning', 'incentive',
            
            // Attendance & Shifts
            'shift', 'shift-assignment', 'attendance', 'holiday',
            
            // Payroll & Finance
            'salary-component', 'tax-rate', 'salary-sheet', 'payroll', 
            'loan', 'fund', 'fund-transaction', 'final-settlement',
            
            // Leave
            'leave-type', 'leave-request', 'leave-encashment',
            
            // Tasks
            'task', 'task-category', 'recurring-task',
            
            // --- UPDATED SECTIONS FOR SEPARATION ---
            
            // 1. Client Management (Business Clients / CRM)
            'client-management', 
            
            // 2. Login Details (Owner's System Credentials - Formerly Client Credential)
            'login-details',
            
            // 3. Tax Services (Tax Filing & Service Management)
            'tax-service'
        ];

        $actions = ['list', 'create', 'edit', 'delete'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                // Creates permissions like: client-management-list, login-details-create, etc.
                Permission::firstOrCreate(['name' => "{$module}-{$action}"]);
            }
        }
        
        // 2. Define Special/Custom Permissions
        // These are actions that don't fit the standard CRUD pattern
        $customPermissions = [
            // Payroll
            'salary-sheet-finalize',
            'salary-sheet-print',
            'salary-sheet-email',
            'salary-sheet-bank-export',
            'payslip-view',
            
            // Employee
            'employee-print',
            'employee-print-contract',
            'employee-import',
            'employee-export',
            
            // Leaves & Approvals
            'leave-request-approve',
            'leave-request-reject',
            'loan-approve',
            'final-settlement-print',
            
            // Tasks
            'task-assign', // Ability to assign tasks to others
            'task-review', // Ability to review completed tasks
            'task-report', // Access to task analytics
            
            // Attendance
            'attendance-bulk-create',
            
            // Tax Services Specific
            'tax-service-manage', // Access to the specific tax client dashboard
        ];

        foreach ($customPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 3. Create Roles (if they don't exist)
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager']); 
        $userRole = Role::firstOrCreate(['name' => 'User']);
        // Add this in your Seeder inside the "3. Create Roles" section:
        $clientRole = Role::firstOrCreate(['name' => 'Client']);

        // Give the Client ONLY the permission to view their own data (we will restrict this in the controller)
        $clientRole->syncPermissions(['tax-service-list']);

        // 4. Assign All Permissions to Owner & Admin
        $allPermissions = Permission::all();
        $ownerRole->syncPermissions($allPermissions);
        $adminRole->syncPermissions($allPermissions);
        
        // 5. Assign Basic Permissions to User (Employee)
        $userPermissions = [
            'leave-request-list', 'leave-request-create',
            'attendance-list', 'attendance-create',
            'task-list', 'payslip-view'
        ];
        // Only sync if the permission actually exists to avoid errors
        $validUserPerms = Permission::whereIn('name', $userPermissions)->get();
        $userRole->syncPermissions($validUserPerms);

        // 6. Ensure the first user is Owner
        $user = User::first();
        if ($user && !$user->hasRole('Owner')) {
            $user->assignRole('Owner');
        }
    }
}