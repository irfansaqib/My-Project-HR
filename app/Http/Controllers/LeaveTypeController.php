<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaveTypes = Auth::user()->business->leaveTypes()->paginate(15);
        return view('leave-types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('leave-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Auth::user()->business->leaveTypes()->create($request->only('name'));

        return redirect()->route('leave-types.index')->with('success', 'Leave Type created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveType $leaveType)
    {
        // Authorization check
        if ($leaveType->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        return view('leave-types.edit', compact('leaveType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        // Authorization check
        if ($leaveType->business_id !== Auth::user()->business_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $leaveType->update($request->only('name'));

        return redirect()->route('leave-types.index')->with('success', 'Leave Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        // Authorization check
        if ($leaveType->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        
        $leaveType->delete();

        return redirect()->route('leave-types.index')->with('success', 'Leave Type deleted successfully.');
    }
}