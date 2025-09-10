<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\EmployeeShiftAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeShiftAssignmentController extends Controller
{
    public function create()
    {
        $business = Auth::user()->business;
        $employees = $business->employees()->where('status', 'active')->orderBy('name')->get();
        
        // ** THIS LINE IS CORRECTED **
        // Changed orderBy('shift_name') to orderBy('name') to match your table
        $shifts = $business->shifts()->orderBy('name')->get();

        return view('shift-assignments.create', compact('employees', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'shift_id'       => 'required|exists:shifts,id',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['employee_ids'] as $employeeId) {
                EmployeeShiftAssignment::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'shift_id' => $validated['shift_id'],
                    ],
                    [
                        'start_date' => $validated['start_date'],
                        'end_date' => $validated['end_date'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('shift-assignments.create')->with('success', 'Shifts assigned successfully.');
    }
}