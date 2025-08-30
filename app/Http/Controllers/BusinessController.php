<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the specified business.
     */
    public function show(Business $business): View
    {
        $this->authorize('view', $business);
        return view('business.show', compact('business'));
    }

    /**
     * Show the form for creating a new business.
     */
    public function create(): View
    {
        return view('business.create');
    }

    /**
     * Store a newly created business.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'legal_name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            // === VALIDATION RULE UPDATED ===
            'business_type' => ['required', Rule::in(['Individual', 'Partnership', 'Company'])],
            'ntn_number' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:businesses',
            'logo' => 'nullable|image|mimes:png,jpg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $business = Business::create([
            'user_id' => Auth::id(),
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

        Auth::user()->update(['business_id' => $business->id]);

        return Redirect::route('business.show', $business)->with('success', 'Business details saved successfully!');
    }

    /**
     * Show the form for editing the business.
     */
    public function edit(Business $business): View
    {
        $this->authorize('update', $business);
        return view('business.edit', compact('business'));
    }

    /**
     * Update the specified business.
     */
    public function update(Request $request, Business $business)
    {
        $this->authorize('update', $business);

        $validatedData = $request->validate([
            'legal_name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            // === VALIDATION RULE UPDATED ===
            'business_type' => ['required', Rule::in(['Individual', 'Partnership', 'Company'])],
            'ntn_number' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => ['required', 'email', 'max:255', Rule::unique('businesses')->ignore($business->id)],
            'logo' => 'nullable|image|mimes:png,jpg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $validatedData['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($validatedData);

        return Redirect::route('business.show', $business)->with('success', 'Business details updated successfully!');
    }
}