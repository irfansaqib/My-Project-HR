<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-view')->only(['index', 'show']);
        $this->middleware('permission:role-create')->only(['create', 'store']);
        $this->middleware('permission:role-edit')->only(['edit', 'update']);
        $this->middleware('permission:role-delete')->only('destroy');
    }

    public function index()
    {
        Gate::authorize('role-view');
        $roles = Role::where('name', '!=', 'Owner')->with('permissions')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        Gate::authorize('role-create');
        $permissions = $this->getGroupedPermissions();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        Gate::authorize('role-create');
        $request->validate(['name' => 'required|string|unique:roles,name', 'permissions' => 'required|array']);
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);
        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        Gate::authorize('role-view');
        $permissions = $role->permissions->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });
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

    public function update(Request $request, Role $role)
    {
        Gate::authorize('role-edit');
        if ($role->name === 'Admin' || $role->name === 'Owner') {
            return redirect()->route('roles.index')->with('error', 'The Admin and Owner roles cannot be edited.');
        }
        $request->validate(['name' => 'required|string|unique:roles,name,'.$role->id, 'permissions' => 'required|array']);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);
        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('role-delete');
        if ($role->name === 'Admin' || $role->name === 'Owner') {
            return redirect()->route('roles.index')->with('error', 'The Admin and Owner roles cannot be deleted.');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
    
    /**
     * THIS HELPER FUNCTION IS NOW MORE ROBUST
     */
    private function getGroupedPermissions()
    {
        $permissions = Permission::all();
        $actions = ['view', 'create', 'edit', 'delete'];
        
        $modulePrefixes = [
            'user', 'role', 'business', 'client-login-credential', 'employee', 'department', 
            'designation', 'salary-component', 'tax-rate', 'salary-sheet', 'payslip', 'payroll', 
            'leave-type', 'leave-application'
        ];

        $grouped = [];
        foreach ($modulePrefixes as $prefix) {
            $modulePermissions = $permissions->filter(fn($p) => str_starts_with($p->name, $prefix . '-'));
            if ($modulePermissions->isNotEmpty()) {
                $standard = [];
                $other = [];
                foreach ($modulePermissions as $p) {
                    $actionName = Str::after($p->name, $prefix . '-');
                    if (in_array($actionName, $actions)) {
                        $standard[$actionName] = $p;
                    } else {
                        $other[] = $p;
                    }
                }
                // THIS IS THE FIX: Ensure both 'standard' and 'other' keys always exist
                $grouped[$prefix] = [
                    'standard' => $standard, 
                    'other' => collect($other)
                ];
            }
        }

        // Manually categorize the grouped permissions into tabs
        $categorized = [
            'Administration' => array_intersect_key($grouped, array_flip(['user', 'role', 'business', 'client-login-credential'])),
            'HR & Payroll' => array_intersect_key($grouped, array_flip(['employee', 'department', 'designation', 'salary-component', 'tax-rate', 'salary-sheet', 'payslip', 'payroll'])),
            'Leave Management' => array_intersect_key($grouped, array_flip(['leave-type', 'leave-application']))
        ];
        
        return $categorized;
    }
}