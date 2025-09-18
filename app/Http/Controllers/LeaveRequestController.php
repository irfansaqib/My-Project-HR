<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(LeaveRequest::class, 'leave_request');
    }

    public function index()
    {
        // ✅ DEFINITIVE FIX: Corrected Auth::user() syntax.
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
        // ✅ DEFINITIVE FIX: Corrected Auth::user() syntax.
        $employee = Auth::user()->employee;
        if (!$employee) {
            abort(403, 'You must be linked to an employee to apply for leave.');
        }

        $employee->load('leaveTypes');
        $leaveTypes = $employee->leaveTypes;

        $this->calculateRemainingLeaves($employee);

        $totalLeavesRemaining = 0;
        foreach ($leaveTypes as $type) {
            $slug = Str::slug($type->name, '_');
            $remainingKey = 'leaves_' . $slug . '_remaining';
            $totalLeavesRemaining += $employee->{$remainingKey} ?? 0;
        }
        $employee->total_leaves_remaining = $totalLeavesRemaining;

        return view('leave-requests.create', compact('employee', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // ✅ DEFINITIVE FIX: Corrected Auth::user() syntax.
        $employee = Auth::user()->employee;
        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $daysRequested = $startDate->diffInDaysFiltered(fn (Carbon $date) => !$date->isSunday(), $endDate) + 1;

        $this->calculateRemainingLeaves($employee);
        
        $slug = Str::slug($leaveType->name, '_');
        $leaveBalanceField = 'leaves_' . $slug . '_remaining';
        
        $currentBalance = $employee->{$leaveBalanceField} ?? 0;
        if ($daysRequested > $currentBalance) {
            return back()->withErrors(['end_date' => "Requested days ($daysRequested) exceeds available balance for {$leaveType->name} ({$currentBalance})."])->withInput();
        }

        $attachmentPath = $request->hasFile('attachment') ? $request->file('attachment')->store('leave_attachments/' . $employee->business_id, 'public') : null;

        LeaveRequest::create([
            'business_id' => $employee->business_id,
            'employee_id' => $employee->id,
            'leave_type' => $leaveType->name,
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
        $employee->load('leaveTypes');
        $leaveTypes = $employee->leaveTypes;
        $this->calculateRemainingLeaves($employee);
        return view('leave-requests.edit', compact('leaveRequest', 'employee', 'leaveTypes'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        
        $data = $request->except(['_token', '_method', 'leave_type_id']);
        $data['leave_type'] = $leaveType->name;

        $leaveRequest->update($data);
        return redirect()->route('leave-requests.index')->with('success', 'Leave application updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $wasApproved = $leaveRequest->status === 'approved';
        DB::transaction(function () use ($leaveRequest, $wasApproved) {
            if ($wasApproved) {
                $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
                $dates = collect($period)->filter(fn($date) => !$date->isSunday())->map(fn($date) => $date->format('Y-m-d'));
                Attendance::where('employee_id', $leaveRequest->employee_id)->whereIn('date', $dates)->where('status', 'leave')->delete();
            }
            $leaveRequest->delete();
        });
        return redirect()->route('leave-requests.index')->with('success', 'Leave application withdrawn successfully.');
    }
    
    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorize('approve', $leaveRequest);
        DB::transaction(function () use ($leaveRequest) {
            $leaveRequest->update(['status' => 'approved', 'approver_id' => Auth::id()]);
            $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
            foreach ($period as $date) {
                if (!$date->isSunday()) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $leaveRequest->employee_id, 'date' => $date->format('Y-m-d')],
                        ['business_id' => $leaveRequest->business_id, 'status' => 'leave', 'check_in' => null, 'check_out' => null]
                    );
                }
            }
        });
        return back()->with('success', 'Leave request has been approved and attendance records are updated.');
    }
    
    public function reject(LeaveRequest $leaveRequest)
    {
        $this->authorize('reject', $leaveRequest);
        $wasApproved = $leaveRequest->status === 'approved';
        DB::transaction(function () use ($leaveRequest, $wasApproved) {
            $leaveRequest->update(['status' => 'rejected', 'approver_id' => Auth::id()]);
            if ($wasApproved) {
                $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
                foreach ($period as $date) {
                    if (!$date->isSunday()) {
                        Attendance::updateOrCreate(
                            ['employee_id' => $leaveRequest->employee_id, 'date' => $date->format('Y-m-d')],
                            ['business_id' => $leaveRequest->business_id, 'status' => 'absent', 'check_in' => null, 'check_out' => null]
                        );
                    }
                }
            }
        });
        return back()->with('success', 'Leave request has been rejected.');
    }

    public function extraCreate()
    {
        $this->authorize('create', LeaveRequest::class);
        // ✅ DEFINITIVE FIX: Corrected Auth::user() syntax.
        $employee = Auth::user()->employee;
        return view('leave-requests.extra_create', compact('employee'));
    }
    
    public function extraStore(Request $request)
    {
        $this->authorize('create', LeaveRequest::class);
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date', 'reason' => 'required|string', 'days_requested' => 'required|integer|min:1']);
        // ✅ DEFINITIVE FIX: Corrected Auth::user() syntax.
        $employee = Auth::user()->employee;
        LeaveRequest::create(['business_id' => $employee->business_id, 'employee_id' => $employee->id, 'leave_type' => 'Extra', 'start_date' => $request->start_date, 'end_date' => $request->end_date, 'reason' => $request->reason, 'status' => 'pending']);
        return redirect()->route('leave-requests.index')->with('success', 'Extra leave application submitted successfully.');
    }
    
    private function calculateRemainingLeaves(Employee $employee)
    {
        $employee->load('leaveTypes');

        foreach ($employee->leaveTypes as $type) {
            $totalAllowed = $type->pivot->days_allotted;
            
            $approvedDays = $employee->leaveRequests()
                ->where('leave_type', $type->name)
                ->where('status', 'approved')
                ->get()
                ->sum(function ($request) {
                    return Carbon::parse($request->start_date)->diffInDaysFiltered(fn (Carbon $date) => !$date->isSunday(), Carbon::parse($request->end_date)) + 1;
                });
            
            $slug = Str::slug($type->name, '_');
            $remainingKey = 'leaves_' . $slug . '_remaining';
            $employee->{$remainingKey} = $totalAllowed - $approvedDays;
        }
    }
}

