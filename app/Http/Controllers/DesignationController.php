<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class DesignationController extends Controller
{
    /**
     * ✅ NEW: Authorize all resource methods using DesignationPolicy
     */
    public function __construct()
    {
        $this->authorizeResource(Designation::class, 'designation');
    }

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

        if ($request->ajax()) {
            return response()->json(['success' => true, 'designation' => $designation]);
        }

        return Redirect::route('designations.index')->with('success', 'Designation created successfully!');
    }

    public function show(Designation $designation)
    {
        return redirect()->route('designations.index');
    }

    public function edit(Designation $designation)
    {
        // ✅ REMOVED: Manual authorization check
        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        // ✅ REMOVED: Manual authorization check

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('designations')->where('business_id', Auth::user()->business_id)->ignore($designation->id)],
        ]);

        $designation->update($validated);

        return Redirect::route('designations.index')->with('success', 'Designation updated successfully!');
    }

    public function destroy(Designation $designation)
    {
        // ✅ REMOVED: Manual authorization check
        
        $designation->delete();

        return Redirect::route('designations.index')->with('success', 'Designation deleted successfully!');
    }
}