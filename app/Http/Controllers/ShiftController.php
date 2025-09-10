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
        $validated = $request->validate([
            'shift_name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_time_minutes' => 'required|integer|min:0',
            'punch_in_window_start' => 'required|date_format:H:i',
            'punch_in_window_end' => 'required|date_format:H:i|after:punch_in_window_start',
            'weekly_off_days' => 'nullable|string',
        ]);

        Auth::user()->business->shifts()->create($validated);

        return redirect()->route('shifts.index')->with('success', 'Shift created successfully.');
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

        $validated = $request->validate([
            'shift_name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_time_minutes' => 'required|integer|min:0',
            'punch_in_window_start' => 'required|date_format:H:i',
            'punch_in_window_end' => 'required|date_format:H:i|after:punch_in_window_start',
            'weekly_off_days' => 'nullable|string',
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