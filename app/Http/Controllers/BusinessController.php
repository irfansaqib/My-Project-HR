<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    public function create()
    {
        $this->authorize('create', Business::class);
        return view('business.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Business::class);
        // ✅ FIX: Corrected all validation field names to match the database
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'ntn_number' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $business = Business::create($validated);
        Auth::user()->update(['business_id' => $business->id]);

        return redirect()->route('dashboard')->with('success', 'Business profile created successfully.');
    }
    
    public function show(Business $business)
    {
        $this->authorize('view', $business);
        return view('business.show', compact('business'));
    }

    public function edit(Business $business)
    {
        $this->authorize('update', $business);
        return view('business.edit', compact('business'));
    }

    public function update(Request $request, Business $business)
    {
        $this->authorize('update', $business);

        // ✅ FIX: Corrected all validation field names to match the database
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'ntn_number' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'business_type' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // ✅ FIX: Added all new fields to the data being saved
        $businessData = $request->only(['business_name', 'legal_name', 'registration_number', 'ntn_number', 'phone_number', 'email', 'business_type', 'address']);

        if ($request->hasFile('logo')) {
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $businessData['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($businessData);

        return redirect()->route('business.show', $business)->with('success', 'Business profile updated successfully.');
    }
}