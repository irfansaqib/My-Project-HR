<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'owner') {
             $leaveRequests = LeaveRequest::whereHas('employee', function ($query) use ($user) {
                $query->where('business_id', $user->business_id);
             })->with('employee')->orderBy('created_at', 'desc')->get();
             return view('leave-requests.index_manager', compact('leaveRequests'));
        }

        $employee = Employee::where('email', $user->email)->first();

        if (!$employee) {
            abort(403, 'You are not registered as an employee to view this page.');
        }
        
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)->orderBy('start_date', 'desc')->get();
        
        $this->calculateRemainingLeaves($employee);

        return view('leave-requests.index', compact('employee', 'leaveRequests'));
    }

    public function create()
    {
        $employee = Employee::where('email', Auth::user()->email)->firstOrFail();
        $this->calculateRemainingLeaves($employee);
        
        $employee->total_leaves_remaining = ($employee->leaves_annual_remaining ?? 0) + ($employee->leaves_sick_remaining ?? 0) + ($employee->leaves_casual_remaining ?? 0) + ($employee->leaves_other_remaining ?? 0);

        return view('leave-requests.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|in:annual,sick,casual,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $employee = Employee::where('email', Auth::user()->email)->firstOrFail();
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        $this->calculateRemainingLeaves($employee);
        $leaveBalanceField = 'leaves_' . $request->leave_type . '_remaining';
        $remainingBalance = $employee->{$leaveBalanceField};

        if ($daysRequested > $remainingBalance) {
            return back()->withErrors([
                'end_date' => 'The number of days requested ('.$daysRequested.') exceeds your available balance ('.$remainingBalance.') for this leave type.'
            ])->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
        ]);

        return Redirect::route('leave-requests.index')->with('success', 'Leave application submitted successfully.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        if (Auth::user()->role !== 'owner' || $leaveRequest->employee->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        $employee = $leaveRequest->employee;
        $this->calculateRemainingLeaves($employee);
        $recentRequests = LeaveRequest::where('employee_id', $employee->id)
                                      ->where('id', '!=', $leaveRequest->id)
                                      ->orderBy('created_at', 'desc')
                                      ->limit(5)
                                      ->get();

        return view('leave-requests.show', compact('leaveRequest', 'employee', 'recentRequests'));
    }

    public function edit(LeaveRequest $leaveRequest)
    {
        $employee = Employee::where('email', Auth::user()->email)->firstOrFail();
        if ($leaveRequest->employee_id !== $employee->id || $leaveRequest->status !== 'pending') {
            abort(403, 'This action is unauthorized.');
        }
        $this->calculateRemainingLeaves($employee);
        return view('leave-requests.edit', compact('leaveRequest', 'employee'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $employee = Employee::where('email', Auth::user()->email)->firstOrFail();
        if ($leaveRequest->employee_id !== $employee->id || $leaveRequest->status !== 'pending') {
            abort(403, 'This action is unauthorized.');
        }
        $validated = $request->validate([
            'leave_type' => 'required|in:annual,sick,casual,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        $leaveRequest->update($validated);
        return Redirect::route('leave-requests.index')->with('success', 'Leave application updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $employee = Employee::where('email', Auth::user()->email)->firstOrFail();
        if ($leaveRequest->employee_id !== $employee->id || $leaveRequest->status !== 'pending') {
            abort(403, 'This action is unauthorized.');
        }
        $leaveRequest->delete();
        return Redirect::route('leave-requests.index')->with('success', 'Leave application withdrawn successfully.');
    }
    
    public function approve(LeaveRequest $leaveRequest)
    {
        if (Auth::user()->role !== 'owner' || $leaveRequest->employee->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        $employee = $leaveRequest->employee;
        if ($leaveRequest->leave_type !== 'extra') {
            $startDate = Carbon::parse($leaveRequest->start_date);
            $endDate = Carbon::parse($leaveRequest->end_date);
            $daysRequested = $startDate->diffInDays($endDate) + 1;
            $leaveBalanceField = 'leaves_' . $leaveRequest->leave_type;
            $leaveBalance = $employee->{$leaveBalanceField};
            if ($daysRequested > $leaveBalance) {
                return Redirect::route('leave-requests.index')->with('error', 'Action failed: Employee does not have enough leave balance.');
            }
            // This logic was incorrect, it should not deduct from the allocated total. The calculation handles it.
            // $employee->{$leaveBalanceField} -= $daysRequested; 
            // $employee->save();
        }
        $leaveRequest->status = 'approved';
        $leaveRequest->approver_id = Auth::id();
        $leaveRequest->save();
        return Redirect::route('leave-requests.index')->with('success', 'Leave request has been approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if (Auth::user()->role !== 'owner' || $leaveRequest->employee->business_id !== Auth::user()->business_id) {
            abort(403);
        }
        $leaveRequest->status = 'rejected';
        $leaveRequest->approver_id = Auth::id();
        $leaveRequest->save();
        return Redirect::route('leave-requests.index')->with('success', 'Leave request has been rejected.');
    }

    public function extraCreate()
    {
        $employee = Employee::where('email', Auth::user()->email)->firstOrFail();
        return view('leave-requests.extra_create', compact('employee'));
    }

    public function extraStore(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date', 'reason' => 'required|string', 'days_requested' => 'required|integer|min:1']);
        $employee = Employee::where('email', Auth::user()->email)->firstOrFail();
        LeaveRequest::create(['employee_id' => $employee->id, 'leave_type' => 'extra', 'start_date' => $request->start_date, 'end_date' => $request->end_date, 'reason' => $request->reason, 'status' => 'pending']);
        return Redirect::route('leave-requests.index')->with('success', 'Extra leave application submitted successfully.');
    }

    public function print(LeaveRequest $leaveRequest)
    {
        if (Auth::user()->role !== 'owner' || $leaveRequest->employee->business_id !== Auth::user()->business_id) { abort(403); }
        if ($leaveRequest->leave_type !== 'extra' || $leaveRequest->status !== 'approved') { abort(403, 'This request is not eligible for printing.'); }
        return view('leave-requests.show_print', compact('leaveRequest'));
    }

    private function calculateRemainingLeaves(Employee $employee)
    {
        $leaveTypes = ['annual', 'sick', 'casual', 'other'];
        foreach ($leaveTypes as $type) {
            $approvedDays = LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type', $type)
                ->where('status', 'approved')
                ->get()
                ->sum(function ($request) {
                    return Carbon::parse($request->start_date)->diffInDays($request->end_date) + 1;
                });
            $allocatedField = 'leaves_' . $type;
            $remainingField = 'leaves_' . $type . '_remaining';
            $employee->{$remainingField} = $employee->{$allocatedField} - $approvedDays;
        }
    }
}