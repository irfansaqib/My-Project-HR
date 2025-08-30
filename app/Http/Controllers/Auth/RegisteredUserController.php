<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Http\RedirectResponse;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'legal_name' => ['required', 'string', 'max:255'], // <-- New validation
            'registration_number' => ['required', 'string', 'max:255'], // <-- New validation
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create the business using the new form data
        $business = Business::create([
            'user_id' => $user->id,
            'business_name' => $request->name . "'s Business", // A sensible default
            'legal_name' => $request->legal_name, // <-- From the form
            'email' => $request->email,
            'registration_number' => $request->registration_number, // <-- From the form
            'business_type' => 'Not Specified',
            'address' => 'Not Specified',
            'phone_number' => 'Not Specified',
        ]);

        // Update the user with their new business_id
        $user->business_id = $business->id;
        $user->save();
        
        // Assign the 'Owner' role to the new user
        $user->assignRole('Owner');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}