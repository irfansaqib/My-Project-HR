<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Warning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller
{
    /**
     * Show the form for creating a new warning for a specific employee.
     */
    public function create(Employee $employee)
    {
        return view('warnings.create', compact('employee'));
    }

    /**
     * Store a newly created warning in storage.
     */
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'warning_date' => 'required|date',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'action_taken' => 'nullable|string', // ✅ NEW
        ]);

        $employee->warnings()->create([
            'business_id' => $employee->business_id,
            'issued_by' => Auth::id(),
            'warning_date' => $request->warning_date,
            'subject' => $request->subject,
            'description' => $request->description,
            'action_taken' => $request->action_taken, // ✅ NEW
            'status' => 'active',
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Warning issued successfully.');
    }

    /**
     * ✅ NEW: Withdraw a warning by changing its status.
     */
    public function destroy(Warning $warning)
    {
        // Add authorization check here if needed in the future
        // $this->authorize('delete', $warning);

        $warning->update(['status' => 'withdrawn']);

        return redirect()->route('employees.show', $warning->employee_id)->with('success', 'Warning has been withdrawn successfully.');
    }
}