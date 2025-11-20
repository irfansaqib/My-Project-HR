<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    /**
     * Display the authenticated user's business profile.
     */
    public function show()
    {
        $business = Auth::user()->business;
        $this->authorize('view', $business);

        return view('business.show', compact('business'));
    }

    /**
     * Show the business edit form.
     */
    public function edit()
    {
        $business = Auth::user()->business;
        $this->authorize('update', $business);

        return view('business.edit', compact('business'));
    }

    /**
     * Update the business information.
     */
    public function update(Request $request)
    {
        $business = Auth::user()->business;
        $this->authorize('update', $business);

        // ✅ Validation
        $validatedData = $request->validate([
            'legal_name'          => 'required|string|max:255',
            'business_name'       => 'required|string|max:255',
            'business_type'       => [
                'nullable',
                'string',
                Rule::in([
                    'Sole Proprietorship',
                    'Partnership',
                    'Private Limited Company',
                    'Public Limited Company',
                    'NGO / Trust',
                ]),
            ],
            'registration_number' => 'nullable|string|max:255',
            'ntn_number'          => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255',
            'phone_number'        => 'nullable|string|max:255',
            'address'             => 'nullable|string|max:500',
            'logo'                => 'nullable|image|max:2048', // max 2MB
        ]);

        // ✅ Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if (!empty($business->logo_path) && Storage::disk('public')->exists($business->logo_path)) {
                Storage::disk('public')->delete($business->logo_path);
            }

            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            $validatedData['logo_path'] = $path;
        }

        // ✅ Never include non-existent 'logo' key
        unset($validatedData['logo']);

        // ✅ Update business record
        $business->update($validatedData);

        return redirect()
            ->route('business.show', $business)
            ->with('success', 'Business details updated successfully.');
    }
}
