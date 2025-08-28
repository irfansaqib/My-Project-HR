<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    /**
     * Display the business details of the authenticated user.
     */
    public function show()
    {
        $business = Auth::user()->business;
        return view('business.show', compact('business'));
    }

    /**
     * Show the form for creating a new business detail.
     */
    public function create()
    {
        return view('business.create');
    }

    /**
     * Store a newly created business detail in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'legal_name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string',
            'ntn_number' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:businesses',
            'logo' => 'nullable|image|mimes:png|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $business = Business::create([
            'user_id' => Auth::id(), // Link business to its owner
            'legal_name' => $validatedData['legal_name'],
            'business_name' => $validatedData['business_name'],
            'business_type' => $validatedData['business_type'],
            'ntn_number' => $validatedData['ntn_number'],
            'registration_number' => $validatedData['registration_number'],
            'address' => $validatedData['address'],
            'phone_number' => $validatedData['phone_number'],
            'email' => $validatedData['email'],
            'logo_path' => $logoPath,
        ]);

        // **Crucial Step**: Update the owner's record with the new business ID
        Auth::user()->update(['business_id' => $business->id]);

        return Redirect::route('dashboard')->with('success', 'Business details saved successfully!');
    }

    /**
     * Show the form for editing the business detail.
     */
    public function edit()
    {
        $business = Auth::user()->business;
        return view('business.edit', compact('business'));
    }

    /**
     * Update the business detail in storage.
     */
    public function update(Request $request)
    {
        $business = Auth::user()->business;

        $validatedData = $request->validate([
            'legal_name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string',
            'ntn_number' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => ['required', 'email', 'max:255', Rule::unique('businesses')->ignore($business->id)],
            'logo' => 'nullable|image|mimes:png|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $validatedData['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($validatedData);

        return Redirect::route('business.show')->with('success', 'Business details updated successfully!');
    }
}