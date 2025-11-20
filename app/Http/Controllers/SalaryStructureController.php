<?php

namespace App\Http\controllers;

use App\Models\Employee;
use App\Models\SalaryComponent; // ✅ ADDED: To fetch component details
use App\Models\SalaryStructure;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{
    /**
     * Show the form for creating a new salary structure for an employee.
     */
    public function create(Employee $employee)
    {
        // This method remains the same as your existing file.
        $employee->load('salaryComponents');
        $latestStructure = $employee->salaryStructures()->latest('effective_date')->first();
        
        $currentStructure = [];
        if ($latestStructure) {
            $currentStructure['basic_salary'] = $latestStructure->basic_salary;
            $componentMap = [];
            foreach ($latestStructure->salary_components as $component) {
                $componentMap[$component['name']] = $component['amount'];
            }
            $currentStructure['components'] = $componentMap;
        } else {
            $currentStructure['basic_salary'] = $employee->basic_salary;
            $componentMap = [];
            foreach ($employee->salaryComponents as $component) {
                $componentMap[$component->name] = $component->pivot->amount;
            }
            $currentStructure['components'] = $componentMap;
        }

        return view('employees.salary.create', [
            'employee' => $employee,
            'currentStructure' => $currentStructure
        ]);
    }

    /**
     * ✅ UPDATED: Store a new salary structure snapshot with a 'pending' status.
     * This method no longer overwrites the main employee record.
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'effective_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'components' => 'nullable|array',
            'components.*' => 'nullable|numeric|min:0',
        ]);

        $componentsData = [];
        if (isset($validated['components'])) {
            // Get the details of the components being updated
            $salaryComponents = SalaryComponent::whereIn('id', array_keys($validated['components']))->get()->keyBy('id');

            foreach ($validated['components'] as $id => $amount) {
                if (isset($salaryComponents[$id])) {
                    $componentsData[] = [
                        'id' => $id,
                        'name' => $salaryComponents[$id]->name,
                        'type' => $salaryComponents[$id]->type,
                        'amount' => (float)$amount,
                    ];
                }
            }
        }

        // Create the new historical snapshot with a 'pending' status.
        SalaryStructure::create([
            'employee_id' => $employee->id,
            'effective_date' => $validated['effective_date'],
            'basic_salary' => $validated['basic_salary'],
            'salary_components' => $componentsData, // Store as a clean JSON array
            'status' => 'pending', // Set the default status for the approval workflow
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Salary revision has been submitted for approval.');
    }
}