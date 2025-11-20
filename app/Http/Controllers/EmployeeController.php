<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Designation;
use App\Models\Department;
use App\Models\SalaryComponent;
use App\Models\LeaveType;
use App\Models\BusinessBankAccount;
use App\Models\SalaryStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $currentStatus = $request->get('status', 'all');
        $employeesQuery = Employee::with(['department', 'designation']);

        if ($currentStatus !== 'all') {
            $employeesQuery->where('status', $currentStatus);
        }

        $employees = $employeesQuery->orderBy('employee_number', 'desc')->paginate(15);
        return view('employees.index', compact('employees', 'currentStatus'));
    }

    public function create()
    {
        $designations = Designation::all();
        $departments  = Department::all();
        $allowances   = SalaryComponent::where('type', 'allowance')->get();
        $deductions   = SalaryComponent::where('type', 'deduction')->get();
        $leaveTypes   = LeaveType::all();
        $businessBankAccounts = BusinessBankAccount::all();
        $componentAmounts = [];

        return view('employees.create', compact(
            'designations', 'departments', 'allowances', 'deductions',
            'leaveTypes', 'businessBankAccounts', 'componentAmounts'
        ));
    }

    public function store(Request $request)
    {
        // 1. Sanitize Salary
        $request->merge([
            'basic_salary' => str_replace(',', '', $request->input('basic_salary')),
        ]);

        // 2. Build Validation Rules
        $rules = [
            'name'         => 'required|string|max:255',
            'cnic'         => 'required|string|max:30',
            'phone'        => 'required|string|max:20',
            'basic_salary' => 'required|numeric|min:0',
            'create_user_account' => 'nullable|boolean',
            
            // ✅ PASSWORD FIX: Completely ignore password unless checkbox is checked
            'password' => 'exclude_unless:create_user_account,1|required|string|min:8|confirmed',
        ];

        // 3. Conditional Email Rules
        if ($request->boolean('create_user_account')) {
            // If creating login: Email is mandatory and must be unique in USERS table
            $rules['email'] = 'required|email|unique:users,email';
        } else {
            // If just employee: Email is optional, but if provided, must be unique in EMPLOYEES table
            $rules['email'] = 'nullable|email|unique:employees,email';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $businessId = Auth::user()->business_id ?? null;
            $employee = new Employee();
            
            $employee->fill($request->except([
                'photo', 'attachment', 'components', 'leaves', 
                'qualifications', 'experiences', 'create_user_account', 'password', 'password_confirmation',
                'gross_salary', 'net_salary'
            ]));

            // Generate ID
            if ($businessId) {
                $employee->employee_number = $this->generateEmployeeNumber($businessId);
            }

            if ($request->hasFile('photo')) {
                $employee->photo_path = $request->file('photo')->store('employees/photos', 'public');
            }
            if ($request->hasFile('attachment')) {
                $employee->attachment_path = $request->file('attachment')->store('employees/attachments', 'public');
            }

            $employee->business_id = $businessId;
            $employee->save();

            // --- Process Salary ---
            $componentsInput = $request->input('components', []);
            $syncData = [];
            $salaryStructureJson = [];
            
            $allowancesTotal = 0;
            $deductionsTotal = 0;
            
            $allComponents = SalaryComponent::whereIn('id', array_keys($componentsInput))->get()->keyBy('id');

            foreach ($componentsInput as $id => $amount) {
                $amount = (float) str_replace([',', '(', ')'], '', (string) $amount);
                if ($amount > 0) {
                    $syncData[$id] = ['amount' => $amount];
                    if ($comp = $allComponents->get($id)) {
                        $salaryStructureJson[] = [
                            'id' => $comp->id, 'name' => $comp->name, 'type' => $comp->type, 'amount' => $amount
                        ];
                        if ($comp->type === 'allowance') $allowancesTotal += $amount;
                        else $deductionsTotal += $amount;
                    }
                }
            }

            $employee->salaryComponents()->sync($syncData);
            $employee->gross_salary = $employee->basic_salary + $allowancesTotal;
            $employee->net_salary = $employee->gross_salary - $deductionsTotal;
            $employee->save();

            // Initial Approved Structure
            SalaryStructure::create([
                'employee_id' => $employee->id,
                'effective_date' => $employee->joining_date ?? now(),
                'basic_salary' => $employee->basic_salary,
                'salary_components' => json_encode($salaryStructureJson),
                'status' => 'approved',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // --- Leaves ---
            $leaves = $request->input('leaves', []);
            if (!empty($leaves)) {
                $leaveSync = [];
                foreach ($leaves as $leaveId => $days) {
                    $leaveSync[$leaveId] = ['days_allotted' => (int) $days];
                }
                $employee->leaveTypes()->sync($leaveSync);
            }

            // --- Quals & Exp ---
            $this->syncQualifications($employee, $request->input('qualifications', []));
            $this->syncExperiences($employee, $request->input('experiences', []));

            // --- User Account ---
            if ($request->boolean('create_user_account') && $request->email) {
                $user = User::create([
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'password' => Hash::make($request->password),
                    'employee_id' => $employee->id,
                    'business_id' => $businessId,
                ]);
                $user->assignRole('Employee');
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee created successfully. ID: ' . $employee->employee_number);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Employee store failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'System Error: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Employee $employee)
    {
        // Self-healing ID
        if (empty($employee->employee_number) && $employee->business_id) {
            $employee->employee_number = $this->generateEmployeeNumber($employee->business_id);
            $employee->save();
        }

        $designations = Designation::all();
        $departments  = Department::all();
        $allowances   = SalaryComponent::where('type', 'allowance')->get();
        $deductions   = SalaryComponent::where('type', 'deduction')->get();
        $leaveTypes   = LeaveType::all();
        $businessBankAccounts = BusinessBankAccount::all();

        $employee->load('qualifications', 'experiences');

        $allComponents = SalaryComponent::where('business_id', Auth::user()->business_id)
                            ->get()->keyBy('name');

        $latestStructure = $employee->salaryStructures()
                            ->where('status', 'approved')
                            ->latest('effective_date')
                            ->first();

        $componentAmounts = [];

        if ($latestStructure) {
            $employee->basic_salary = $latestStructure->basic_salary;
            $componentsInJson = json_decode($latestStructure->salary_components, true);
            if (is_array($componentsInJson)) {
                foreach ($componentsInJson as $comp) {
                    $componentModel = $allComponents->get($comp['name']);
                    if ($componentModel) {
                        $componentAmounts[$componentModel->id] = (float) $comp['amount'];
                    }
                }
            }
        } else {
            $employee->load('salaryComponents');
            foreach ($employee->salaryComponents as $comp) {
                $componentAmounts[$comp->id] = (float) $comp->pivot->amount;
            }
        }

        return view('employees.edit', compact(
            'employee', 'designations', 'departments', 'allowances',
            'deductions', 'leaveTypes', 'businessBankAccounts', 'componentAmounts'
        ));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->merge([
            'basic_salary' => str_replace(',', '', $request->input('basic_salary')),
        ]);

        $request->validate([
            'name'         => 'required|string|max:255',
            'cnic'         => 'required|string|max:30',
            'email'        => 'nullable|email',
            'phone'        => 'required|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $employee->fill($request->except([
                'photo', 'attachment', 'components', 'leaves', 
                'qualifications', 'experiences', 
                'basic_salary', 'gross_salary', 'net_salary',
                'create_user_account', 'password'
            ]));

            if (empty($employee->employee_number) && $employee->business_id) {
                $employee->employee_number = $this->generateEmployeeNumber($employee->business_id);
            }

            if ($request->hasFile('photo')) {
                if ($employee->photo_path) Storage::disk('public')->delete($employee->photo_path);
                $employee->photo_path = $request->file('photo')->store('employees/photos', 'public');
            }
            if ($request->hasFile('attachment')) {
                if ($employee->attachment_path) Storage::disk('public')->delete($employee->attachment_path);
                $employee->attachment_path = $request->file('attachment')->store('employees/attachments', 'public');
            }
            $employee->save();

            $leaves = $request->input('leaves', []);
            if (!empty($leaves)) {
                $leaveSync = [];
                foreach ($leaves as $leaveId => $days) {
                    $leaveSync[$leaveId] = ['days_allotted' => (int) $days];
                }
                $employee->leaveTypes()->sync($leaveSync);
            }

            $this->syncQualifications($employee, $request->input('qualifications', []));
            $this->syncExperiences($employee, $request->input('experiences', []));

            DB::commit();
            return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Employee update failed: ' . $e->getMessage());
            return back()->withErrors('Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Employee $employee)
    {
        if (empty($employee->employee_number) && $employee->business_id) {
            $employee->employee_number = $this->generateEmployeeNumber($employee->business_id);
            $employee->save();
        }

        $employee->loadMissing([
            'designationRelation', 'departmentRelation', 'qualifications',
            'experiences', 'leaveTypes', 'warnings.issuer', 'salaryComponents'
        ]);

        $allowancesList = [];
        $deductionsList = [];

        foreach ($employee->salaryComponents as $comp) {
            $entry = ['model' => $comp, 'amount' => (float) $comp->pivot->amount];
            if ($comp->type === 'allowance') {
                $allowancesList[] = $entry;
            } else {
                $deductionsList[] = $entry;
            }
        }

        $computedGross = $employee->gross_salary; 
        $computedNet   = $employee->net_salary;

        return view('employees.show', [
            'employee'      => $employee,
            'allowances'    => $allowancesList,
            'deductions'    => $deductionsList,
            'computedGross' => $computedGross,
            'computedNet'   => $computedNet,
        ]);
    }

    public function print(Employee $employee)
    {
        $employee->loadMissing([
            'designationRelation', 'departmentRelation', 'qualifications',
            'experiences', 'leaveTypes', 'warnings.issuer', 'salaryComponents'
        ]);

        $allowancesList = [];
        $deductionsList = [];

        foreach ($employee->salaryComponents as $comp) {
            $entry = ['model' => $comp, 'amount' => (float) $comp->pivot->amount];
            if ($comp->type === 'allowance') $allowancesList[] = $entry;
            else $deductionsList[] = $entry;
        }

        $computedGross = $employee->gross_salary; 
        $computedNet   = $employee->net_salary;
        $business = Auth::user()->business;

        return view('employees.print', [
            'employee' => $employee, 'business' => $business,
            'allowances' => $allowancesList, 'deductions' => $deductionsList,
            'computedGross' => $computedGross, 'computedNet' => $computedNet,
        ]);
    }
    
    public function printContract(Employee $employee)
    {
        $employee->loadMissing(['designationRelation', 'departmentRelation']);
        $business = Auth::user()->business;
        return view('employees.contract', compact('employee', 'business'));
    }

    public function destroy(Employee $employee)
    {
        try {
            // Delete Associated User Account
            if ($employee->user) {
                $employee->user->delete();
            }

            if ($employee->photo_path) Storage::disk('public')->delete($employee->photo_path);
            if ($employee->attachment_path) Storage::disk('public')->delete($employee->attachment_path);
            $employee->delete();
            
            return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors('Error deleting employee: ' . $e->getMessage());
        }
    }

    private function syncQualifications(Employee $employee, array $inputs)
    {
        $keepIds = [];
        foreach ($inputs as $row) {
            if (isset($row['id']) && !empty($row['id'])) $keepIds[] = $row['id'];
        }
        $employee->qualifications()->whereNotIn('id', $keepIds)->delete();
        foreach ($inputs as $row) {
            if (!empty($row['degree_title']) && !empty($row['institute'])) {
                $employee->qualifications()->updateOrCreate(
                    ['id' => $row['id'] ?? null],
                    ['degree_title' => $row['degree_title'], 'institute' => $row['institute'], 'year_of_passing' => $row['year_of_passing'] ?? null]
                );
            }
        }
    }

    private function syncExperiences(Employee $employee, array $inputs)
    {
        $keepIds = [];
        foreach ($inputs as $row) {
            if (isset($row['id']) && !empty($row['id'])) $keepIds[] = $row['id'];
        }
        $employee->experiences()->whereNotIn('id', $keepIds)->delete();
        foreach ($inputs as $row) {
            if (!empty($row['company_name']) && !empty($row['job_title'])) {
                $employee->experiences()->updateOrCreate(
                    ['id' => $row['id'] ?? null],
                    ['company_name' => $row['company_name'], 'job_title' => $row['job_title'], 'from_date' => $row['from_date'] ?? null, 'to_date' => $row['to_date'] ?? null]
                );
            }
        }
    }

    private function generateEmployeeNumber(int $businessId): string
    {
        $prefix = 'Emp-' . date('Y') . '-';
        $lastEmployee = Employee::where('business_id', $businessId)
            ->where('employee_number', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(employee_number) DESC')
            ->orderBy('employee_number', 'desc')
            ->first();

        if ($lastEmployee) {
            $lastNumber = (int) str_replace($prefix, '', $lastEmployee->employee_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}