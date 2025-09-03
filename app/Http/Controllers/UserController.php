<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Added 'user-view' to the show method
        $this->middleware('permission:user-view')->only(['index', 'show']);
        $this->middleware('permission:user-create')->only(['create', 'store']);
        $this->middleware('permission:user-edit')->only(['edit', 'update']);
        $this->middleware('permission:user-delete')->only('destroy');
    }

    public function index()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'Owner');
        })->with('roles')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $employees = Employee::whereDoesntHave('user')->get();
        $roles = Role::where('name', '!=', 'Owner')->pluck('name', 'name');
        $permissions = Permission::all()->groupBy(fn($p) => explode('-', $p->name)[0]);
        return view('users.create', compact('employees', 'roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:users,employee_id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'employee_id' => $request->employee_id,
            'business_id' => auth()->user()->business_id,
        ]);

        $user->assignRole($request->role);

        if ($request->role === 'User' && $request->has('permissions')) {
            $user->givePermissionTo($request->permissions);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }
    
    /**
     * ADDED THIS METHOD
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions', 'employee');
        $permissions = $user->permissions->groupBy(fn($p) => explode('-', $p->name)[0]);
        return view('users.show', compact('user', 'permissions'));
    }

    public function edit(User $user)
    {
        $employees = Employee::whereDoesntHave('user')->orWhere('id', $user->employee_id)->get();
        $roles = Role::where('name', '!=', 'Owner')->pluck('name', 'name');
        $permissions = Permission::all()->groupBy(fn($p) => explode('-', $p->name)[0]);
        return view('users.edit', compact('user', 'employees', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id|unique:users,employee_id,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $user->update($request->only('name', 'email', 'employee_id'));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        $user->syncRoles($request->role);
        
        if ($request->role === 'User' && $request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        } else {
            $user->syncPermissions([]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}