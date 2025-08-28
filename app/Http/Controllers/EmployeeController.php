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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $designations = Designation::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        $departments = Department::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('employees.create', compact('designations', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Personal & Contact Info
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
            // Employment Info
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'status' => 'required|string',
            // Salary & Leaves
            'basic_salary' => 'nullable|numeric|min:0',
            'house_rent' => 'nullable|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'medical' => 'nullable|numeric|min:0',
            'conveyance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'leaves_sick' => 'nullable|integer|min:0',
            'leaves_casual' => 'nullable|integer|min:0',
            'leaves_annual' => 'nullable|integer|min:0',
            'leaves_other' => 'nullable|integer|min:0',
            'leave_period_from' => 'nullable|date',
            'leave_period_to' => 'nullable|date|after_or_equal:leave_period_from',
            // Attachments
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            // Dynamic Fields
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
            $photoPath = $request->file('photo')->store('employee_photos', 'public');
        }
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('employee_attachments', 'public');
        }

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

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        if ($employee->business_id !== Auth::user()->business_id) { abort(403); }
        $employee->load('qualifications', 'experiences');
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        if ($employee->business_id !== Auth::user()->business_id) { abort(403); }
        $employee->load('qualifications', 'experiences');
        $designations = Designation::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        $departments = Department::where('business_id', Auth::user()->business_id)->orderBy('name')->get();
        return view('employees.edit', compact('employee', 'designations', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        if ($employee->business_id !== Auth::user()->business_id) { abort(403); }

        $validated = $request->validate([
            // Personal & Contact Info
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
            // Employment Info
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'status' => 'required|string',
             // Salary & Leaves
            'basic_salary' => 'nullable|numeric|min:0',
            'house_rent' => 'nullable|numeric|min:0',
            'utilities' => 'nullable|numeric|min:0',
            'medical' => 'nullable|numeric|min:0',
            'conveyance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'leaves_sick' => 'nullable|integer|min:0',
            'leaves_casual' => 'nullable|integer|min:0',
            'leaves_annual' => 'nullable|integer|min:0',
            'leaves_other' => 'nullable|integer|min:0',
            'leave_period_from' => 'nullable|date',
            'leave_period_to' => 'nullable|date|after_or_equal:leave_period_from',
            // Attachments
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            // Dynamic Fields
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
            $validated['photo_path'] = $request->file('photo')->store('employee_photos', 'public');
        }
        if ($request->hasFile('attachment')) {
            if ($employee->attachment_path) { Storage::disk('public')->delete($employee->attachment_path); }
            $validated['attachment_path'] = $request->file('attachment')->store('employee_attachments', 'public');
        }

        $employee->update($validated);

        // Sync Qualifications
        $employee->qualifications()->delete();
        if ($request->filled('qualifications')) {
            $employee->qualifications()->createMany($request->qualifications);
        }
        // Sync Experiences
        $employee->experiences()->delete();
        if ($request->filled('experiences')) {
            $employee->experiences()->createMany($request->experiences);
        }

        return Redirect::route('employees.index')->with('success', 'Employee updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        if ($employee->business_id !== Auth::user()->business_id) { abort(403); }
        if ($employee->photo_path) { Storage::disk('public')->delete($employee->photo_path); }
        if ($employee->attachment_path) { Storage::disk('public')->delete($employee->attachment_path); }
        $employee->delete();
        return Redirect::route('employees.index')->with('success', 'Employee deleted successfully!');
    }
    
    /**
     * Show the printable view for the specified employee.
     */
    public function print(Employee $employee)
    {
        if ($employee->business_id !== Auth::user()->business_id) { abort(403); }
        $business = Auth::user()->business;
        return view('employees.print', compact('employee', 'business'));
    }
}