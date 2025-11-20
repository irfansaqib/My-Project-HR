<?php

namespace App\Http\Controllers;

use App\Models\BusinessBankAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\LeaveType;
use App\Models\SalaryComponent;
use App\Models\User;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    /**
     * FINAL FIX: Correctly filters employees by status and eager-loads designation.
     */
    public function index(Request $request)
    {
        $businessId = Auth::user()->business_id;
        $currentStatus = $request->input('status', 'active');

        $employeesQuery = Employee::where('business_id', $businessId)->with(['designation']);

        if ($currentStatus !== 'all') {
            // This was the typo. It's now corrected to use the $currentStatus variable.
            $employeesQuery->where('status', $currentStatus);
        }

        $employees = $employeesQuery->orderBy('name')->paginate(25);

        return view('employees.index', compact('employees', 'currentStatus'));
    }

    public function create()
    {
        $businessId = Auth::user()->business_id;
        $departments = Department::where('business_id', $businessId)->get();
        $designations = Designation::where('business_id', $businessId)->get();
        $salaryComponents = SalaryComponent::where('business_id', $businessId)->get();
        $bankAccounts = BusinessBankAccount::where('business_id', $businessId)->get();
        $leaveTypes = LeaveType::where('business_id', $businessId)->get();
        
        return view('employees.create', compact('departments', 'designations', 'salaryComponents', 'bankAccounts', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request);
        $businessId = Auth::user()->business_id;
        $validated['business_id'] = $businessId;

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('employee_photos/' . $businessId, 'public');
        }
        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('employee_attachments/' . $businessId, 'public');
        }
        
        $validated['employee_number'] = $this->generateEmployeeNumber($businessId);

        DB::transaction(function () use ($validated, $request) {
            $employee = Employee::create($validated);
            
            // For a new employee, set the start date for all components to their joining date.
            $componentsWithDate = $this->formatComponents($validated['salary_components'] ?? [], $validated['joining_date']);
            if (!empty($componentsWithDate)) {
                $employee->salaryComponents()->sync($componentsWithDate);
            }
            if (isset($validated['leave_types'])) {
                $employee->leaveTypes()->sync($this->formatLeaveTypes($validated['leave_types']));
            }

            if ($request->create_user_account) {
                User::create([
                    'name' => $employee->name, 'email' => $employee->email,
                    'password' => Hash::make($request->password), 'business_id' => $employee->business_id,
                    'employee_id' => $employee->id,
                ]);
            }
        });

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    /**
     * FINAL FIX: Ensures all necessary relationships, including designation and department, are loaded correctly.
     */
    public function show(Employee $employee, TaxCalculatorService $taxCalculator)
    {
        $this->authorize('view', $employee);
        
        $employee->load([
            'designation',
            'department',
            'qualifications', 
            'experiences', 
            'leaveTypes', 
            'warnings.issuer', 
            'salaryComponents' => function ($query) {
                $query->where(function($q) {
                    $q->whereNull('employee_salary_component.end_date')
                      ->orWhere('employee_salary_component.end_date', '>=', now());
                });
            }
        ]);

        $monthlyTax = 0;
        try {
            $monthlyTax = $taxCalculator->calculate($employee, Carbon::now());
        } catch (\Exception $e) {
            Log::error("Tax calculation failed on employee profile for employee ID {$employee->id}: " . $e->getMessage());
        }
        
        return view('employees.show', compact('employee', 'monthlyTax'));
    }

    public function edit(Employee $employee)
    {
        $this->authorize('update', $employee);
        $businessId = Auth::user()->business_id;
        $departments = Department::where('business_id', $businessId)->get();
        $designations = Designation::where('business_id', $businessId)->get();
        $salaryComponents = SalaryComponent::where('business_id', $businessId)->get();
        $bankAccounts = BusinessBankAccount::where('business_id', $businessId)->get();
        $leaveTypes = LeaveType::where('business_id', $businessId)->get();

        $employee->load(['salaryComponents' => function ($query) {
                $query->wherePivotNull('end_date')->orWherePivot('end_date', '>=', now());
        }, 'leaveTypes']);

        return view('employees.edit', compact('employee', 'departments', 'designations', 'salaryComponents', 'bankAccounts', 'leaveTypes'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);
        $validated = $this->validateEmployee($request, $employee);

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) Storage::disk('public')->delete($employee->photo_path);
            $validated['photo_path'] = $request->file('photo')->store('employee_photos/' . $employee->business_id, 'public');
        }
        if ($request->hasFile('attachment')) {
            if ($employee->attachment_path) Storage::disk('public')->delete($employee->attachment_path);
            $validated['attachment_path'] = $request->file('attachment')->store('employee_attachments/' . $employee->business_id, 'public');
        }

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete', $employee);
        if ($employee->photo_path) Storage::disk('public')->delete($employee->photo_path);
        if ($employee->attachment_path) Storage::disk('public')->delete($employee->attachment_path);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function print(Employee $employee, TaxCalculatorService $taxCalculator)
    {
        $this->authorize('view', $employee);
        $business = Auth::user()->business;
        $monthlyTax = 0;
        try {
            $monthlyTax = $taxCalculator->calculate($employee, Carbon::now());
        } catch (\Exception $e) {
            Log::error("Tax calculation failed for printing for employee ID {$employee->id}: " . $e->getMessage());
        }
        return view('employees.print', compact('employee', 'business', 'monthlyTax'));
    }
    
    public function printContract(Employee $employee)
    {
        $this->authorize('view', $employee);
        $business = Auth::user()->business;
        return view('employees.contract', compact('employee', 'business'));
    }
    
    public function getShiftForDate(Employee $employee, $date)
    {
        $this->authorize('view', $employee);
        try {
            $targetDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json(['found' => false, 'error' => 'Invalid date format'], 400);
        }
        $employeeShifts = EmployeeShift::where('employee_id', $employee->id)
            ->where(function ($query) use ($targetDate) {
                $query->where('start_date', '<=', $targetDate)
                      ->where(function ($q) use ($targetDate) {
                          $q->where('end_date', '>=', $targetDate)
                            ->orWhereNull('end_date');
                      });
            })
            ->with('shift')
            ->get();
        if ($employeeShifts->isEmpty()) {
            return response()->json(['found' => false]);
        }
        $shifts = $employeeShifts->map(function($employeeShift) {
            if (!$employeeShift->shift) return null;
            return ['start_time' => Carbon::parse($employeeShift->shift->start_time)->format('H:i'), 'end_time' => Carbon::parse($employeeShift->shift->end_time)->format('H:i')];
        })->filter();
        return response()->json(['found' => true, 'shifts' => $shifts->values()]);
    }

    public function createExit(Employee $employee)
    {
        $this->authorize('update', $employee);
        return view('employees.exit.create', compact('employee'));
    }

    public function storeExit(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);
        $validated = $request->validate([
            'exit_date' => 'required|date',
            'exit_type' => 'required|string|in:resigned,terminated,retired',
            'exit_reason' => 'required|string|max:1000',
        ]);
        $employee->update(['status' => $validated['exit_type'], 'exit_date' => $validated['exit_date'], 'exit_type' => $validated['exit_type'], 'exit_reason' => $validated['exit_reason']]);
        return redirect()->route('employees.show', $employee)->with('success', 'Employee exit has been processed successfully.');
    }
    
    private function validateEmployee(Request $request, Employee $employee = null): array
    {
        $businessId = Auth::user()->business_id;
        $emailRule = ['required', 'string', 'email', Rule::unique('employees', 'email')->where('business_id', $businessId)];
        if ($employee) {
            $emailRule[2]->ignore($employee->id);
        }
        return $request->validate([
            'name' => 'required|string|max:255', 'father_name' => 'nullable|string|max:255', 'email' => $emailRule,
            'phone' => 'required|string|max:20', 'cnic' => ['required', 'string', Rule::unique('employees')->where('business_id', $businessId)->ignore($employee->id ?? null)],
            'dob' => 'nullable|date', 'gender' => 'required|string', 'address' => 'nullable|string', 'joining_date' => 'required|date',
            'department_id' => ['required', Rule::exists('departments', 'id')->where('business_id', $businessId)],
            'designation_id' => ['required', Rule::exists('designations', 'id')->where('business_id', $businessId)],
            'job_description' => 'nullable|string', 'basic_salary' => 'required|numeric|min:0', 'gross_salary' => 'required|numeric|min:0', 'net_salary' => 'required|numeric|min:0',
            'probation_period' => 'nullable|integer|min:0', 'bank_account_title' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255', 'bank_name' => 'nullable|string|max:255', 'bank_branch' => 'nullable|string|max:255',
            'business_bank_account_id' => ['nullable', Rule::exists('business_bank_accounts', 'id')->where('business_id', $businessId)],
            'emergency_contact_name' => 'nullable|string|max:255', 'emergency_contact_relation' => 'nullable|string|max:255', 'emergency_contact_phone' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048', 'attachment' => 'nullable|file|max:5120', 'salary_components' => 'nullable|array', 'leave_types' => 'nullable|array',
            'create_user_account' => 'nullable|boolean', 'password' => 'nullable|string|min:8|confirmed',
        ]);
    }

    private function generateEmployeeNumber(int $businessId): string
    {
        $prefix = 'Emp-' . date('Y') . '-';
        $lastEmployee = Employee::where('business_id', $businessId)->where('employee_number', 'like', $prefix . '%')->orderBy('employee_number', 'desc')->first();
        if ($lastEmployee) {
            $lastNumber = (int)str_replace($prefix, '', $lastEmployee->employee_number);
            return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }
        return $prefix . '0001';
    }

    private function formatComponents(array $components, $startDate): array
    {
        $syncData = [];
        foreach ($components as $id => $amount) {
            if (!empty($amount)) {
                $syncData[$id] = ['amount' => $amount, 'start_date' => $startDate];
            }
        }
        return $syncData;
    }

    private function formatLeaveTypes(array $leaveTypes): array
    {
        $syncData = [];
        foreach ($leaveTypes as $id => $data) {
            if (!empty($data['days_allotted'])) {
                $syncData[$id] = ['days_allotted' => $data['days_allotted']];
            }
        }
        return $syncData;
    }
}