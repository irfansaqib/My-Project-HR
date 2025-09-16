<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
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
        // ✅ DEFINITIVE FIX: Added validation for the new field.
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
        if ($shift->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        return view('shifts.show', compact('shift'));
    }
    
    public function edit(Shift $shift)
    {
        if ($shift->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        return view('shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        if ($shift->business_id !== Auth::user()->business_id) {
            abort(403);
        }

        // ✅ DEFINITIVE FIX: Added validation for the new field.
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
        if ($shift->business_id !== Auth::user()->business_id) {
            abort(403);
        }

        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully.');
    }
}

