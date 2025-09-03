<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    // ... index(), create(), store() methods are unchanged ...
    public function index(){ Gate::authorize('role-view'); $roles = Role::where('name', '!=', 'Owner')->paginate(10); return view('roles.index', compact('roles')); }
    public  function create(){ Gate::authorize('role-create'); $permissions = $this->getGroupedPermissions(); return view('roles.create', compact('permissions')); }
    public function store(Request $request){ /* ... */ }

    public function show(Role $role)
    {
        Gate::authorize('role-view');
        // Use the new helper for the show view as well
        $permissions = $this->getGroupedPermissions($role->permissions);
        return view('roles.show', compact('role', 'permissions'));
    }

    public function edit(Role $role)
    {
        Gate::authorize('role-edit');
        if ($role->name === 'Admin' || $role->name === 'Owner') {
            return redirect()->route('roles.index')->with('error', 'The Admin and Owner roles cannot be edited.');
        }
        $permissions = $this->getGroupedPermissions();
        return view('roles.edit', compact('role', 'permissions'));
    }

    // ... update() and destroy() methods are unchanged ...
    public function update(Request $request, Role $role){ /* ... */ }
    public function destroy(Role $role){ /* ... */ }
    
    /**
     * THIS IS THE NEW, SMARTER HELPER FUNCTION
     */
    private function getGroupedPermissions($existingPermissions = null)
    {
        $permissions = $existingPermissions ?? Permission::all();
        
        // Define module prefixes, from longest to shortest, to ensure correct matching
        $modulePrefixes = [
            'client-login-credential', 'salary-component', 'leave-application', 
            'leave-type', 'tax-rate', 'salary-sheet',
            'employee', 'department', 'designation', 'payroll', 'payslip', 'business', 'user', 'role'
        ];

        $grouped = [];
        foreach($modulePrefixes as $prefix) {
            $group = $permissions->filter(fn($p) => str_starts_with($p->name, $prefix . '-'));
            if ($group->isNotEmpty()) {
                $grouped[$prefix] = $group;
            }
        }

        // Manually categorize the grouped permissions into tabs
        $categorized = [
            'Administration' => [
                'user' => $grouped['user'] ?? collect(),
                'role' => $grouped['role'] ?? collect(),
                'business' => $grouped['business'] ?? collect(),
                'client-login-credential' => $grouped['client-login-credential'] ?? collect(),
            ],
            'HR & Payroll' => [
                'employee' => $grouped['employee'] ?? collect(),
                'department' => $grouped['department'] ?? collect(),
                'designation' => $grouped['designation'] ?? collect(),
                'salary-component' => $grouped['salary-component'] ?? collect(),
                'tax-rate' => $grouped['tax-rate'] ?? collect(),
                'salary-sheet' => $grouped['salary-sheet'] ?? collect(),
                'payslip' => $grouped['payslip'] ?? collect(),
                'payroll' => $grouped['payroll'] ?? collect(),
            ],
            'Leave Management' => [
                 'leave-type' => $grouped['leave-type'] ?? collect(),
                 'leave-application' => $grouped['leave-application'] ?? collect(),
            ]
        ];
        
        return $categorized;
    }
}