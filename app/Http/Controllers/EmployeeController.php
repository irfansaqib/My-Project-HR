<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveType;
use App\Models\Qualification;
use App\Models\Experience;
use App\Models\SalaryComponent;
use App\Models\BusinessBankAccount;
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
        $business = Auth::user()->business;
        $departments = $business->departments()->orderBy('name')->get();
        $designations = $business->designations()->orderBy('name')->get();
        $allowances = SalaryComponent::where('type', 'allowance')->orderBy('name')->get();
        $deductions = SalaryComponent::where('type', 'deduction')->orderBy('name')->get();
        $businessBankAccounts = $business->bankAccounts()->get();
        $leaveTypes = $business->leaveTypes()->orderBy('name')->get();

        return view('employees.create', compact('departments', 'designations', 'allowances', 'deductions', 'businessBankAccounts', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:employees,email']);

        DB::transaction(function () use ($request) {
            $department = Department::firstOrCreate(['name' => $request->department, 'business_id' => auth()->user()->business_id]);
            $designation = Designation::firstOrCreate(['name' => $request->designation, 'business_id' => auth()->user()->business_id]);

            $employeeData = $request->except(['components', 'qualifications', 'experiences', 'department', 'designation', 'leaves']);
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
        $employee->load('qualifications', 'experiences', 'salaryComponents', 'leaveTypes');

        $taxCalculator = resolve(TaxCalculatorService::class);
        $monthlyTax = $taxCalculator->calculate($employee, now());
        
        return view('employees.show', compact('employee', 'monthlyTax'));
    }

    public function edit(Employee $employee)
    {
        $business = Auth::user()->business;
        $departments = $business->departments()->orderBy('name')->get();
        $designations = $business->designations()->orderBy('name')->get();
        $allowances = SalaryComponent::where('type', 'allowance')->get();
        $deductions = SalaryComponent::where('type', 'deduction')->get();
        $businessBankAccounts = $business->bankAccounts()->get();
        $leaveTypes = $business->leaveTypes()->orderBy('name')->get();
        
        return view('employees.edit', compact('employee', 'departments', 'designations', 'allowances', 'deductions', 'businessBankAccounts', 'leaveTypes'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:employees,email,'.$employee->id]);

        DB::transaction(function () use ($request, $employee) {
            $department = Department::firstOrCreate(['name' => $request->department, 'business_id' => auth()->user()->business_id]);
            $designation = Designation::firstOrCreate(['name' => $request->designation, 'business_id' => auth()->user()->business_id]);
            
            $employeeData = $request->except(['components', 'qualifications', 'experiences', 'department', 'designation', 'leaves']);
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
        
        $leavesToSync = [];
        if ($request->has('leaves')) {
            foreach ($request->leaves as $id => $days) {
                if (!is_null($days) && is_numeric($days)) {
                    $leavesToSync[$id] = ['days_allotted' => $days];
                }
            }
        }
        $employee->leaveTypes()->sync($leavesToSync);

        if ($request->has('qualifications')) {
             $existingQualIds = $employee->qualifications->pluck('id')->all();
            $newQualIds = [];
            foreach ($request->qualifications as $qualData) {
                if (isset($qualData['id']) && in_array($qualData['id'], $existingQualIds)) {
                    $qual = Qualification::find($qualData['id']);
                    if ($qual) $qual->update($qualData);
                    $newQualIds[] = $qualData['id'];
                } else {
                    $newQual = $employee->qualifications()->create($qualData);
                    $newQualIds[] = $newQual->id;
                }
            }
            Qualification::destroy(array_diff($existingQualIds, $newQualIds));
        }

        if ($request->has('experiences')) {
            $existingExpIds = $employee->experiences->pluck('id')->all();
            $newExpIds = [];
            foreach ($request->experiences as $expData) {
                if (isset($expData['id']) && in_array($expData['id'], $existingExpIds)) {
                    $exp = Experience::find($expData['id']);
                    if ($exp) $exp->update($expData);
                    $newExpIds[] = $expData['id'];
                } else {
                    $newExp = $employee->experiences()->create($expData);
                    $newExpIds[] = $newExp->id;
                }
            }
            Experience::destroy(array_diff($existingExpIds, $newExpIds));
        }
    }

    public function print(Employee $employee)
    {
        $employee->load('qualifications', 'experiences', 'salaryComponents', 'leaveTypes');
        $business = Business::find(Auth::user()->business_id);

        // ** THIS LOGIC HAS BEEN ADDED **
        $taxCalculator = resolve(TaxCalculatorService::class);
        $monthlyTax = $taxCalculator->calculate($employee, now());

        return view('employees.print', compact('employee', 'business', 'monthlyTax'));
    }

    public function printContract(Employee $employee)
    {
        $business = Business::find(Auth::user()->business_id);
        return view('employees.contract', compact('employee', 'business'));
    }
}