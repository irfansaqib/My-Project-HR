<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        return view('designations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('designations')->where('business_id', Auth::user()->business_id)],
        ]);

        $designation = Designation::create([
            'name' => $validated['name'],
            'business_id' => Auth::user()->business_id,
        ]);

        // If the request is an AJAX request from the modal, return JSON
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'designation' => $designation]);
        }

        // Otherwise, redirect back to the main list
        return Redirect::route('designations.index')->with('success', 'Designation created successfully!');
    }

    public function show(Designation $designation)
    {
        // Not needed for this simple module, redirect to index
        return redirect()->route('designations.index');
    }

    public function edit(Designation $designation)
    {
        if ($designation->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        if ($designation->business_id !== Auth::user()->business_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('designations')->where('business_id', Auth::user()->business_id)->ignore($designation->id)],
        ]);

        $designation->update($validated);

        return Redirect::route('designations.index')->with('success', 'Designation updated successfully!');
    }

    public function destroy(Designation $designation)
    {
        if ($designation->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        
        $designation->delete();

        return Redirect::route('designations.index')->with('success', 'Designation deleted successfully!');
    }
}