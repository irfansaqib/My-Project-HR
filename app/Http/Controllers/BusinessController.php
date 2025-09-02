<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function __construct()
    {
        // This will apply authorization to all methods
        $this->authorizeResource(Business::class, 'business');
    }

    /**
     * Display the specified resource.
     */
    public function show(Business $business)
    {
        return view('business.show', compact('business'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // For 'create', we don't have a business model yet, so we can't authorize on it.
        // We can check if the user is authorized to 'create' a business in general.
        $this->authorize('create', Business::class);
        return view('business.create');
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        return view('business.edit', compact('business'));
    }

    /**
     * Update the specified resource in storage.
     */
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

        return redirect()->route('dashboard')->with('success', 'Business details updated successfully!');
    }
}