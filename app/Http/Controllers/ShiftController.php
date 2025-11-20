<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    /**
     * ✅ NEW: Authorize all resource methods using ShiftPolicy
     */
    public function __construct()
    {
        $this->authorizeResource(Shift::class, 'shift');
    }

    public function index()
    {
        $shifts = Auth::user()->business->shifts()->paginate(15);
        return view('shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'date_format:H:i'],
            'grace_period_in_minutes' => 'required|integer|min:0',
            'auto_deduct_minutes' => 'required|integer|min:0',
            'punch_in_window_start' => 'required|date_format:H:i',
            'punch_in_window_end' => ['required', 'date_format:H:i'],
            'weekly_off' => 'nullable|string',
        ]);

        Auth::user()->business->shifts()->create($validated);

        return redirect()->route('shifts.index')->with('success', 'Shift created successfully.');
    }

    public function show(Shift $shift)
    {
        // ✅ REMOVED: Manual authorization check
        return view('shifts.show', compact('shift'));
    }
    
    public function edit(Shift $shift)
    {
        // ✅ REMOVED: Manual authorization check
        return view('shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        // ✅ REMOVED: Manual authorization check

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'date_format:H:i'],
            'grace_period_in_minutes' => 'required|integer|min:0',
            'auto_deduct_minutes' => 'required|integer|min:0',
            'punch_in_window_start' => 'required|date_format:H:i',
            'punch_in_window_end' => ['required', 'date_format:H:i'],
            'weekly_off' => 'nullable|string',
        ]);

        $shift->update($validated);

        return redirect()->route('shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift)
    {
        // ✅ REMOVED: Manual authorization check

        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully.');
    }
}