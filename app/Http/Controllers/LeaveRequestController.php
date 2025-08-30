<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(LeaveRequest::class, 'leave_request');
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole(['Owner', 'Admin'])) {
            $leaveRequests = LeaveRequest::with('employee')->latest()->paginate(15);
            return view('leave-requests.index_manager', compact('leaveRequests'));
        }

        $employee = $user->employee;
        if (!$employee) {
            abort(403, 'You must be linked to an employee to manage leave.');
        }

        $this->calculateRemainingLeaves($employee);
        $leaveRequests = $employee->leaveRequests()->latest()->get();

        return view('leave-requests.index', compact('employee', 'leaveRequests'));
    }

    public function create()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            abort(403, 'You must be linked to an employee to apply for leave.');
        }

        $this->calculateRemainingLeaves($employee);
        $employee->total_leaves_remaining = ($employee->leaves_annual_remaining ?? 0) + ($employee->leaves_sick_remaining ?? 0) + ($employee->leaves_casual_remaining ?? 0) + ($employee->leaves_other_remaining ?? 0);

        return view('leave-requests.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:annual,sick,casual,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $employee = Auth::user()->employee;
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $daysRequested = $startDate->diffInDaysFiltered(fn (Carbon $date) => !$date->isSunday(), $endDate) + 1;

        $this->calculateRemainingLeaves($employee);
        $leaveBalanceField = 'leaves_' . $request->leave_type . '_remaining';
        
        if ($daysRequested > $employee->{$leaveBalanceField}) {
            return back()->withErrors(['end_date' => "Requested days ($daysRequested) exceeds available balance ({$employee->{$leaveBalanceField}})."])->withInput();
        }

        $attachmentPath = $request->hasFile('attachment') ? $request->file('attachment')->store('leave_attachments/' . $employee->business_id, 'public') : null;

        // THE FIX IS HERE: We now explicitly add the business_id when creating the request.
        LeaveRequest::create([
            'business_id' => $employee->business_id,
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave application submitted successfully.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $employee = $leaveRequest->employee;
        $this->calculateRemainingLeaves($employee);
        return view('leave-requests.show', compact('leaveRequest', 'employee'));
    }

    public function edit(LeaveRequest $leaveRequest)
    {
        $employee = Auth::user()->employee;
        $this->calculateRemainingLeaves($employee);
        return view('leave-requests.edit', compact('leaveRequest', 'employee'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // ... validation and update logic ...
        $leaveRequest->update($request->all());
        return redirect()->route('leave-requests.index')->with('success', 'Leave application updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();
        return redirect()->route('leave-requests.index')->with('success', 'Leave application withdrawn successfully.');
    }
    
    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorize('approve', $leaveRequest);
        $leaveRequest->update(['status' => 'approved', 'approver_id' => Auth::id()]);
        return back()->with('success', 'Leave request has been approved.');
    }

    public function reject(LeaveRequest $leaveRequest)
    {
        $this->authorize('reject', $leaveRequest);
        $leaveRequest->update(['status' => 'rejected', 'approver_id' => Auth::id()]);
        return back()->with('success', 'Leave request has been rejected.');
    }

    public function extraCreate()
    {
        $this->authorize('create', LeaveRequest::class);
        $employee = Auth::user()->employee;
        return view('leave-requests.extra_create', compact('employee'));
    }
    
    public function extraStore(Request $request)
    {
        $this->authorize('create', LeaveRequest::class);
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date', 'reason' => 'required|string', 'days_requested' => 'required|integer|min:1']);
        $employee = Auth::user()->employee;
        
        LeaveRequest::create([
            'business_id' => $employee->business_id,
            'employee_id' => $employee->id,
            'leave_type' => 'extra',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);
        
        return redirect()->route('leave-requests.index')->with('success', 'Extra leave application submitted successfully.');
    }
    
    private function calculateRemainingLeaves(Employee $employee)
    {
        $leaveTypes = ['annual', 'sick', 'casual', 'other'];
        foreach ($leaveTypes as $type) {
            $approvedDays = $employee->leaveRequests()
                ->where('leave_type', $type)
                ->where('status', 'approved')
                ->get()
                ->sum(function ($request) {
                    return Carbon::parse($request->start_date)->diffInDaysFiltered(fn (Carbon $date) => !$date->isSunday(), Carbon::parse($request->end_date)) + 1;
                });
            $employee->{'leaves_' . $type . '_remaining'} = ($employee->{'leaves_' . $type} ?? 0) - $approvedDays;
        }
    }
}