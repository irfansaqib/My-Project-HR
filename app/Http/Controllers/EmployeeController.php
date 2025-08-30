<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Designation;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:employee-view')->only(['index', 'show', 'print', 'printContract']);
        $this->middleware('permission:employee-create')->only(['create', 'store']);
        $this->middleware('permission:employee-edit')->only(['edit', 'update']);
        $this->middleware('permission:employee-delete')->only('destroy');
    }

    public function index()
    {
        $employees = Employee::orderBy('name')->get();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $designations = Designation::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        $departments = Department::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('employees.create', compact('designations', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'cnic' => ['required', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/', Rule::unique('employees')->where('business_id', Auth::user()->business_id)],
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('employees')->where('business_id', Auth::user()->business_id)],
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'status' => 'required|string',
            'probation_period' => 'nullable|integer|min:0',
            'job_description' => 'nullable|string',
            'basic_salary' => 'nullable|numeric|min:0',
            'house_rent' => 'nullable|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'medical' => 'nullable|numeric|min:0',
            'conveyance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'bank_account_title' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'leaves_sick' => 'nullable|integer|min:0',
            'leaves_casual' => 'nullable|integer|min:0',
            'leaves_annual' => 'nullable|integer|min:0',
            'leaves_other' => 'nullable|integer|min:0',
            'leave_period_from' => 'nullable|date',
            'leave_period_to' => 'nullable|date|after_or_equal:leave_period_from',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'qualifications' => 'nullable|array',
            'qualifications.*.degree_title' => 'required_with:qualifications|string|max:255',
            'qualifications.*.institute' => 'required_with:qualifications|string|max:255',
            'qualifications.*.year_of_passing' => 'required_with:qualifications|numeric|digits:4',
            'experiences' => 'nullable|array',
            'experiences.*.company_name' => 'required_with:experiences|string|max:255',
            'experiences.*.job_title' => 'required_with:experiences|string|max:255',
            'experiences.*.from_date' => 'required_with:experiences|date',
            'experiences.*.to_date' => 'required_with:experiences|date|after_or_equal:experiences.*.from_date',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('employee_photos/' . Auth::user()->business_id, 'public');
        }
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('employee_attachments/' . Auth::user()->business_id, 'public');
        }

        unset($validated['photo'], $validated['attachment'], $validated['qualifications'], $validated['experiences']);

        if (empty($validated['joining_date'])) {
            $validated['joining_date'] = Carbon::now()->toDateString();
        }

        $year = date('Y');
        $lastEmployee = Employee::where('business_id', Auth::user()->business_id)->where('employee_number', 'like', 'Emp-'.$year.'-%')->orderBy('employee_number', 'desc')->first();
        $newSerial = $lastEmployee ? (int) substr($lastEmployee->employee_number, -4) + 1 : 1;
        $employeeNumber = 'Emp-' . $year . '-' . str_pad($newSerial, 4, '0', STR_PAD_LEFT);

        $dataToSave = array_merge($validated, ['business_id' => Auth::user()->business_id, 'employee_number' => $employeeNumber, 'photo_path' => $photoPath, 'attachment_path' => $attachmentPath]);
        
        $employee = Employee::create($dataToSave);

        if ($request->filled('qualifications')) {
            $employee->qualifications()->createMany($request->qualifications);
        }
        if ($request->filled('experiences')) {
            $employee->experiences()->createMany($request->experiences);
        }

        return Redirect::route('employees.index')->with('success', 'Employee created successfully!');
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $designations = Designation::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        $departments = Department::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('employees.edit', compact('employee', 'designations', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'cnic' => ['required', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/', Rule::unique('employees')->where('business_id', Auth::user()->business_id)->ignore($employee->id)],
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('employees')->where('business_id', Auth::user()->business_id)->ignore($employee->id)],
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'status' => 'required|string',
            'probation_period' => 'nullable|integer|min:0',
            'job_description' => 'nullable|string',
            'basic_salary' => 'nullable|numeric|min:0',
            'house_rent' => 'nullable|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'medical' => 'nullable|numeric|min:0',
            'conveyance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'bank_account_title' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'leaves_sick' => 'nullable|integer|min:0',
            'leaves_casual' => 'nullable|integer|min:0',
            'leaves_annual' => 'nullable|integer|min:0',
            'leaves_other' => 'nullable|integer|min:0',
            'leave_period_from' => 'nullable|date',
            'leave_period_to' => 'nullable|date|after_or_equal:leave_period_from',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            'qualifications' => 'nullable|array',
            'qualifications.*.degree_title' => 'required_with:qualifications|string|max:255',
            'qualifications.*.institute' => 'required_with:qualifications|string|max:255',
            'qualifications.*.year_of_passing' => 'required_with:qualifications|numeric|digits:4',
            'experiences' => 'nullable|array',
            'experiences.*.company_name' => 'required_with:experiences|string|max:255',
            'experiences.*.job_title' => 'required_with:experiences|string|max:255',
            'experiences.*.from_date' => 'required_with:experiences|date',
            'experiences.*.to_date' => 'required_with:experiences|date|after_or_equal:experiences.*.from_date',
        ]);
        
        if ($request->hasFile('photo')) {
            if ($employee->photo_path) { Storage::disk('public')->delete($employee->photo_path); }
            $validated['photo_path'] = $request->file('photo')->store('employee_photos/' . Auth::user()->business_id, 'public');
        }
        if ($request->hasFile('attachment')) {
            if ($employee->attachment_path) { Storage::disk('public')->delete($employee->attachment_path); }
            $validated['attachment_path'] = $request->file('attachment')->store('employee_attachments/' . Auth::user()->business_id, 'public');
        }
        
        unset($validated['photo'], $validated['attachment'], $validated['qualifications'], $validated['experiences']);

        $employee->update($validated);

        $employee->qualifications()->delete();
        if ($request->filled('qualifications')) {
            $employee->qualifications()->createMany($request->qualifications);
        }
        $employee->experiences()->delete();
        if ($request->filled('experiences')) {
            $employee->experiences()->createMany($request->experiences);
        }

        return Redirect::route('employees.index')->with('success', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->photo_path) { Storage::disk('public')->delete($employee->photo_path); }
        if ($employee->attachment_path) { Storage::disk('public')->delete($employee->attachment_path); }
        $employee->delete();
        return Redirect::route('employees.index')->with('success', 'Employee deleted successfully!');
    }
    
    public function print(Employee $employee)
    {
        $business = Auth::user()->business;
        return view('employees.print', compact('employee', 'business'));
    }

    public function printContract(Employee $employee)
    {
        $business = Auth::user()->business;
        return view('employees.contract', compact('employee', 'business'));
    }
}