<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class ClientAuthController extends Controller
{
    public function showLogin() {
        return view('client_portal.auth.login');
    }

    public function showRegister() {
        return view('client_portal.auth.register');
    }

    // --- CUSTOM LOGIN LOGIC ---
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            // Ensure they are actually a Client
            if (!Auth::user()->hasRole('Client')) {
                Auth::logout();
                return back()->withErrors(['email' => 'Access denied. This portal is for Clients only.']);
            }
            return redirect()->route('client.dashboard');
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
    }

    // --- THE CRITICAL REGISTRATION LOGIC ---
    public function register(Request $request)
    {
        // 1. Validation (Matches Admin Logic)
        $idRules = ['required'];
        if ($request->id_type === 'CNIC') {
            $idRules[] = 'regex:/^[0-9]{13}$/'; 
        } else {
            $idRules[] = 'regex:/^[A-Za-z0-9]{7}-[A-Za-z0-9]{1}$/';
        }

        $request->validate([
            'business_name' => 'required',
            'id_type'       => 'required|in:NTN,CNIC',
            'ntn_cnic'      => $idRules,
            'contact_person'=> 'required',
            'email'         => 'required|email', // Check uniqueness manually below
            'password'      => 'required|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // 2. Check if Client Profile Exists (Anchored by NTN/CNIC)
            $existingClient = Client::where('ntn_cnic', $request->ntn_cnic)->first();

            // 3. Scenario: Admin already added this client
            if ($existingClient) {
                if ($existingClient->user_id) {
                    // User account already exists -> Fail
                    return back()->withErrors(['email' => 'An account already exists for this NTN/CNIC. Please Login.'])->withInput();
                }
                
                // Client exists but no Login -> LINK THEM
                // First, check if email is taken by someone else
                if (User::where('email', $request->email)->exists()) {
                    return back()->withErrors(['email' => 'This email is already in use.'])->withInput();
                }

                $user = User::create([
                    'name' => $request->contact_person,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'business_id' => $existingClient->business_id, // Link to same business
                    'role' => 'Client'
                ]);
                $user->assignRole('Client');

                // Update Client Record
                $existingClient->update(['user_id' => $user->id]);
                
            } else {
                // 4. Scenario: Brand New Client
                if (User::where('email', $request->email)->exists()) {
                    return back()->withErrors(['email' => 'This email is already in use.'])->withInput();
                }

                // Assuming default business_id = 1 for self-signup or you might need a logic 
                // to decide which Agency Business they belong to if you run multiple.
                // For now, we assume the system owner's business ID is 1.
                $defaultBusinessId = 1; 

                $user = User::create([
                    'name' => $request->contact_person,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'business_id' => $defaultBusinessId,
                    'role' => 'Client'
                ]);
                $user->assignRole('Client');

                Client::create([
                    'business_id' => $defaultBusinessId,
                    'user_id' => $user->id,
                    'business_name' => $request->business_name,
                    'id_type' => $request->id_type,
                    'ntn_cnic' => $request->ntn_cnic,
                    'contact_person' => $request->contact_person,
                    'email' => $request->email,
                    'status' => 'active'
                ]);
            }

            DB::commit();
            Auth::login($user);
            return redirect()->route('client.dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // --- GOOGLE AUTH PLACEHOLDERS ---
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback() {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                Auth::login($user);
                return redirect()->route('client.dashboard');
            } else {
                // If user doesn't exist, we can't auto-create because we need NTN/CNIC.
                // Redirect to register with email pre-filled
                return redirect()->route('client.register', ['email' => $googleUser->getEmail()])
                    ->with('info', 'Please complete your registration details.');
            }
        } catch (\Exception $e) {
            return redirect()->route('client.login')->with('error', 'Google Login Failed');
        }
    }
}