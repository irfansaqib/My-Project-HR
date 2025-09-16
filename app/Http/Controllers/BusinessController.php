<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function show()
    {
        $business = Auth::user()->business;
        $this->authorize('view', $business);
        return view('business.show', compact('business'));
    }

    public function edit()
    {
        $business = Auth::user()->business;
        $this->authorize('update', $business);
        return view('business.edit', compact('business'));
    }

    public function update(Request $request)
    {
        $business = Auth::user()->business;
        $this->authorize('update', $business);

        // ✅ DEFINITIVE FIX: Updated validation to match the new dropdown options.
        $validatedData = $request->validate([
            'legal_name'          => 'required|string|max:255',
            'business_name'       => 'required|string|max:255',
            'business_type'       => ['nullable', 'string', Rule::in(['Sole Proprietorship', 'Partnership', 'Private Limited Company', 'Public Limited Company', 'NGO / Trust'])],
            'registration_number' => 'nullable|string|max:255',
            'ntn_number'          => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255',
            'phone_number'        => 'nullable|string|max:255',
            'address'             => 'nullable|string',
            'logo'                => 'nullable|image|max:2048', // 2MB Max
        ]);

        if ($request->hasFile('logo')) {
            $validatedData['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($validatedData);

        return redirect()->route('business.show', $business)->with('success', 'Business details updated successfully.');
    }
}

