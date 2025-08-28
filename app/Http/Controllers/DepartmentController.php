<?php

namespace App\Http\Controllers; // Corrected this line

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->where('business_id', Auth::user()->business_id)],
        ]);

        $department = Department::create([
            'name' => $validated['name'],
            'business_id' => Auth::user()->business_id,
        ]);

        // If the request is from our script, return JSON
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'department' => $department]);
        }

        return Redirect::route('departments.index')->with('success', 'Department created successfully!');
    }

    public function edit(Department $department)
    {
        if ($department->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        if ($department->business_id !== Auth::user()->business_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->where('business_id', Auth::user()->business_id)->ignore($department->id)],
        ]);

        $department->update($validated);

        return Redirect::route('departments.index')->with('success', 'Department updated successfully!');
    }

    public function destroy(Department $department)
    {
        if ($department->business_id !== Auth::user()->business_id) {
            abort(403);
        }

        $department->delete();

        return Redirect::route('departments.index')->with('success', 'Department deleted successfully!');
    }
}