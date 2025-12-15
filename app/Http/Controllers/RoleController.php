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
        $this->middleware('permission:role-list')->only(['index', 'show']);
        $this->middleware('permission:role-create')->only(['create', 'store']);
        $this->middleware('permission:role-edit')->only(['edit', 'update']);
        $this->middleware('permission:role-delete')->only('destroy');
    }

    public function index()
    {
        // We hide 'Owner' so it can't be deleted accidentally
        $roles = Role::where('name', '!=', 'Owner')->with('permissions')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        // Gate::authorize('role-create'); // Redundant due to middleware, but okay to keep
        $permissions = $this->getGroupedPermissions();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:roles,name', 'permissions' => 'required|array']);
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);
        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $permissions = $role->permissions->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });
        return view('roles.show', compact('role', 'permissions'));
    }

    public function edit(Role $role)
    {
        if ($role->name === 'Admin' || $role->name === 'Owner') {
            return redirect()->route('roles.index')->with('error', 'The Admin and Owner roles cannot be edited.');
        }
        $permissions = $this->getGroupedPermissions();
        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
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
        if ($role->name === 'Admin' || $role->name === 'Owner') {
            return redirect()->route('roles.index')->with('error', 'The Admin and Owner roles cannot be deleted.');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
    
    /**
     * Logic to group permissions into TABS for the View
     */
    private function getGroupedPermissions()
    {
        $permissions = Permission::all();
        $standardActions = ['list', 'view', 'create', 'edit', 'delete'];

        // 1. Define the Tabs and which modules go inside them
        $tabs = [
            'Administration' => ['user', 'role', 'business', 'email-configuration'],
            'HR Management'  => ['employee', 'department', 'designation', 'employee-exit', 'warning', 'incentive'],
            'Payroll & Finance' => ['salary-component', 'tax-rate', 'salary-sheet', 'payroll', 'loan', 'fund', 'fund-transaction', 'final-settlement'],
            'Leave & Attendance' => ['shift', 'shift-assignment', 'attendance', 'holiday', 'leave-type', 'leave-request', 'leave-encashment'],
            'Task Management' => ['task', 'task-category', 'recurring-task'],
            'Clients & Services' => ['client', 'client-credential', 'tax-service']
        ];

        $categorized = [];

        foreach ($tabs as $tabName => $prefixes) {
            $tabPermissions = [];

            foreach ($prefixes as $prefix) {
                // Find permissions starting with this prefix (e.g., 'task-')
                $modulePerms = $permissions->filter(function($p) use ($prefix) {
                    return str_starts_with($p->name, $prefix . '-');
                });

                if ($modulePerms->isNotEmpty()) {
                    $standard = [];
                    $other = [];

                    foreach ($modulePerms as $p) {
                        // Extract the action (e.g., 'create' from 'task-create')
                        $actionName = Str::after($p->name, $prefix . '-');
                        
                        if (in_array($actionName, $standardActions)) {
                            $standard[$actionName] = $p;
                        } else {
                            $other[] = $p;
                        }
                    }

                    // Fix: Ensure 'list' maps to 'view' for the checkbox column if 'view' is missing
                    if(!isset($standard['list']) && isset($standard['view'])) {
                         $standard['list'] = $standard['view'];
                    }

                    $tabPermissions[$prefix] = [
                        'standard' => $standard,
                        'other' => collect($other)
                    ];
                }
            }
            if (!empty($tabPermissions)) {
                $categorized[$tabName] = $tabPermissions;
            }
        }
        
        return $categorized;
    }
}