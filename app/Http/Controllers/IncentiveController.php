<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Incentive;
use Illuminate\Http\Request;

class IncentiveController extends Controller
{
    /**
     * Display a list of BONUSES for an employee.
     */
    public function index(Employee $employee)
    {
        // ✅ UPDATED: Now only fetches records with type 'bonus'.
        $incentives = $employee->incentives()->where('type', 'bonus')->orderBy('effective_date', 'desc')->get();
        return view('employees.incentives.index', compact('employee', 'incentives'));
    }

    /**
     * Show the form for creating a new bonus.
     */
    public function create(Employee $employee)
    {
        return view('employees.incentives.create', compact('employee'));
    }

    /**
     * Store a new bonus in the database.
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'effective_date' => 'required|date',
            'description' => 'required|string',
            'increment_amount' => 'required|numeric|min:0',
        ]);

        // Automatically set the type to 'bonus'.
        $validated['type'] = 'bonus';

        $employee->incentives()->create($validated);

        return redirect()->route('employees.incentives.index', $employee)->with('success', 'Bonus added successfully.');
    }

    /**
     * ✅ NEW: Show the form for editing a bonus.
     */
    public function edit(Employee $employee, Incentive $incentive)
    {
        if ($incentive->employee_id !== $employee->id || $incentive->type !== 'bonus') {
            abort(404);
        }
        return view('employees.incentives.edit', compact('employee', 'incentive'));
    }

    /**
     * ✅ NEW: Update a bonus in the database.
     */
    public function update(Request $request, Employee $employee, Incentive $incentive)
    {
        if ($incentive->employee_id !== $employee->id || $incentive->type !== 'bonus') {
            abort(404);
        }
        
        $validated = $request->validate([
            'effective_date' => 'required|date',
            'description' => 'required|string',
            'increment_amount' => 'required|numeric|min:0',
        ]);

        $incentive->update($validated);

        return redirect()->route('employees.incentives.index', $employee)->with('success', 'Bonus updated successfully.');
    }
}