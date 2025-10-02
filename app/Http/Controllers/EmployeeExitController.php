<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeExitController extends Controller
{
    /**
     * Show the form for processing an employee's exit.
     */
    public function create(Employee $employee)
    {
        // Add authorization checks here later if needed
        return view('employees.exit.create', compact('employee'));
    }

    /**
     * Store the exit details and update the employee's status.
     */
    public function store(Request $request, Employee $employee)
    {
        // Add authorization checks here later if needed
        $request->validate([
            'exit_date' => 'required|date',
            'exit_type' => 'required|string|in:resigned,terminated,retired,other',
            'exit_reason' => 'required|string',
        ]);

        // Update the employee's status and exit details
        $employee->update([
            'status' => $request->exit_type,
            'exit_date' => $request->exit_date,
            'exit_type' => $request->exit_type,
            'exit_reason' => $request->exit_reason,
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee exit has been processed successfully.');
    }
}