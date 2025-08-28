<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        // Only show users from the same business
        $users = User::where('business_id', Auth::user()->business_id)->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee', // Team members are 'employee'
            'business_id' => Auth::user()->business_id, // Assign the owner's business ID
        ]);

        return Redirect::route('users.index')->with('success', 'User created successfully!');
    }

    // ... edit, update, destroy methods ...
}