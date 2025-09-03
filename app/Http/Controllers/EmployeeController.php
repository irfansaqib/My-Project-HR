<?php

namespace App\Http\Controllers; // CORRECTED: Replaced hyphen with backslash

use App\Models\Business;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Qualification;
use App\Models\Experience;
use App\Models\SalaryComponent;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['department', 'designation'])->latest()->paginate(10);
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        $allowances = SalaryComponent::where('type', 'allowance')->orderBy('name')->get();
        $deductions = SalaryComponent::where('type', 'deduction')->orderBy('name')->get();
        return view('employees.create', compact('departments', 'designations', 'allowances', 'deductions'));
    }

    public function store(Request $request)
    {
        // Validation can be expanded based on your form fields
        $request->validate(['name' => 'required|string|max:255']);

        DB::transaction(function () use ($request) {
            $department = Department::firstOrCreate(['name' => $request->department, 'business_id' => auth()->user()->business_id]);
            $designation = Designation::firstOrCreate(['name' => $request->designation, 'business_id' => auth()->user()->business_id]);

            $employeeData = $request->except(['components', 'qualifications', 'experiences', 'department', 'designation']);
            $employeeData['business_id'] = auth()->user()->business_id;
            $employeeData['department_id'] = $department->id;
            $employeeData['designation_id'] = $designation->id;
            
            $employee = Employee::create($employeeData);
            
            $this->updateRelated($request, $employee);
        });
        
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }
    
    public function show(Employee $employee)
    {
        $employee->load('qualifications', 'experiences', 'salaryComponents');
        $taxCalculator = resolve(TaxCalculatorService::class);
        $monthlyTax = $taxCalculator->calculate($employee, now());
        
        return view('employees.show', compact('employee', 'monthlyTax'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        $allowances = SalaryComponent::where('type', 'allowance')->get();
        $deductions = SalaryComponent::where('type', 'deduction')->get();
        
        return view('employees.edit', compact('employee', 'departments', 'designations', 'allowances', 'deductions'));
    }

    public function update(Request $request, Employee $employee)
    {
        // Validation can be expanded based on your form fields
        $request->validate(['name' => 'required|string|max:255']);

        DB::transaction(function () use ($request, $employee) {
            $department = Department::firstOrCreate(['name' => $request->department, 'business_id' => auth()->user()->business_id]);
            $designation = Designation::firstOrCreate(['name' => $request->designation, 'business_id' => auth()->user()->business_id]);
            
            $employeeData = $request->except(['components', 'qualifications', 'experiences', 'department', 'designation']);
            $employeeData['department_id'] = $department->id;
            $employeeData['designation_id'] = $designation->id;

            $employee->update($employeeData);
            
            $this->updateRelated($request, $employee);
        });

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    protected function updateRelated(Request $request, Employee $employee)
    {
        $componentsToSync = [];
        if ($request->has('components')) {
            foreach ($request->components as $id => $amount) {
                if (!is_null($amount)) {
                    $componentsToSync[$id] = ['amount' => $amount];
                }
            }
        }
        $employee->salaryComponents()->sync($componentsToSync);
        
        if ($request->has('qualifications')) { /* ... logic for qualifications ... */ }
        if ($request->has('experiences')) { /* ... logic for experiences ... */ }
    }

    // --- ADDED MISSING METHODS ---
    public function print(Employee $employee)
    {
        $business = Business::find(Auth::user()->business_id);
        return view('employees.print', compact('employee', 'business'));
    }

    public function printContract(Employee $employee)
    {
        $business = Business::find(Auth::user()->business_id);
        return view('employees.contract', compact('employee', 'business'));
    }
}