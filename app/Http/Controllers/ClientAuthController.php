<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\Business; // Added for the Business Code check
use Laravel\Socialite\Facades\Socialite; 

class ClientAuthController extends Controller
{
    // ==========================================
    // REGISTER METHODS
    // ==========================================

    public function showRegisterForm()
    {
        if (view()->exists('client_portal.auth.register')) {
            return view('client_portal.auth.register');
        }
        return view('auth.register'); 
    }

    public function register(Request $request)
    {
        // ==========================================
        // 1. SANITIZE INPUT (Strip Dashes from CNIC)
        // ==========================================
        if ($request->has('cnic') && $request->cnic !== null) {
            $request->merge(['cnic' => str_replace('-', '', $request->cnic)]);
        }

        // ==========================================
        // 2. SMART DUPLICATE CHECK
        // ==========================================
        $incomingId = ($request->business_type === 'Individual') ? $request->cnic : $request->ntn;
        
        $alreadyExists = User::where(function($query) use ($incomingId) {
            if ($incomingId) {
                $query->where('cnic', $incomingId)->orWhere('ntn', $incomingId);
            }
        })->exists();

        if ($alreadyExists && $incomingId) {
            return back()->withErrors([
                'duplicate' => 'This ID (CNIC or NTN) is already registered with us.'
            ])->withInput();
        }

        // ==========================================
        // 3. DEFINE RULES
        // ==========================================
        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users',
            'password'      => 'required|string|min:8|confirmed',
            'business_type' => 'required|in:Individual,Partnership,Company',
        ];

        if ($request->business_type === 'Individual') {
            // CNIC: Must be exactly 13 digits
            $rules['cnic'] = 'required|digits:13|unique:users,cnic';
            // NTN: Optional, strict format
            $rules['ntn']  = ['nullable', 'string', 'regex:/^[A-Za-z0-9]{7}-\d{1}$/', 'unique:users,ntn']; 
            $rules['registration_number'] = 'nullable';
        } else {
            // Company Rules
            $rules['registration_number'] = 'required|string|max:50|unique:users,registration_number';
            $rules['ntn']  = ['required', 'string', 'regex:/^[A-Za-z0-9]{7}-\d{1}$/', 'unique:users,ntn']; 
            $rules['cnic'] = 'nullable';
        }

        $validated = $request->validate($rules, [
            'ntn.regex' => 'The NTN format is invalid. It must be 7 characters, a dash, and 1 digit (e.g. 1234567-8).',
            'cnic.digits' => 'The CNIC must be exactly 13 digits.',
        ]);

        // ==========================================
        // 4. CREATE USER
        // ==========================================
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'business_type' => $request->business_type,
            'cnic'          => ($request->business_type === 'Individual') ? $request->cnic : null,
            'registration_number' => ($request->business_type !== 'Individual') ? $request->registration_number : null,
            'ntn'           => $request->ntn, 
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('Client');
        }

        // ==========================================
        // 5. CREATE CLIENT PROFILE (SMART LINK LOGIC)
        // ==========================================
        
        // A. Look for the code in the form input (from URL ?code=...)
        $code = $request->input('business_code');
        
        $business = null;
        if ($code) {
            // If code exists, try to find the specific business
            $business = Business::where('portal_code', $code)->first();
        }

        // B. Fallback: If no code or invalid code, default to the Main Business (First one)
        if (!$business) {
            $business = Business::orderBy('id', 'asc')->first();
        }

        // C. Safety Catch
        if (!$business) {
             // In the rare case the businesses table is empty, we must stop to prevent crash
             return back()->with('error', 'System Error: No Business Profile found to link account.');
        }

        // D. Determine Main ID Type
        if ($request->business_type === 'Individual') {
            $mainId = $request->cnic;
            $idType = 'CNIC';
        } else {
            $mainId = $request->registration_number;
            $idType = 'REG_NO';
        }

        // E. Create Client
        Client::create([
            'user_id'       => $user->id,
            'business_id'   => $business->id, // <--- Correctly linked ID
            'business_name' => $request->name, 
            'contact_person'=> ($request->business_type === 'Individual') ? $request->name : 'N/A',
            'id_type'       => $idType,
            'ntn_cnic'      => $mainId, 
            'status'        => 'active',
            'email'         => $request->email,
        ]);

        Auth::login($user);
        return redirect()->route('client.dashboard')->with('success', 'Account created successfully!');
    } 

    // ==========================================
    // LOGIN METHODS
    // ==========================================

    public function showLoginForm()
    {
        if (view()->exists('client_portal.auth.login')) {
            return view('client_portal.auth.login');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('client.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                return redirect()->route('client.login')->with('error', 'Please register via the form first to set up your profile.');
            }

            Auth::login($user);
            return redirect()->route('client.dashboard');

        } catch (\Exception $e) {
            return redirect()->route('client.login')->with('error', 'Google Login Failed');
        }
    }
}