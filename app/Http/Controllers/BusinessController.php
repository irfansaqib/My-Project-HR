<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Business::class, 'business');
    }

    /**
     * THIS IS THE NEW METHOD to display the business profile.
     */
    public function show(Business $business)
    {
        return view('business.show', compact('business'));
    }

    public function create()
    {
        $this->authorize('create', Business::class);
        return view('business.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Business::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $business = Business::create($validated);

        $user = Auth::user();
        $user->business_id = $business->id;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Business details saved successfully!');
    }

    public function edit(Business $business)
    {
        return view('business.edit', compact('business'));
    }

    public function update(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $business->update($validated);

        return redirect()->route('business.show', $business)->with('success', 'Business details updated successfully!');
    }
}