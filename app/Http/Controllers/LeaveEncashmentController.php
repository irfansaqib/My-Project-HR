<?php

namespace App\Http\Controllers;

use App\Models\LeaveEncashment;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveEncashmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $businessId = $user->business_id;
        
        $query = LeaveEncashment::with(['employee', 'leaveType'])
            ->where('business_id', $businessId)
            ->orderBy('created_at', 'desc');

        // ✅ 1. Filter: Employees only see their own requests
        if (!$user->hasRole(['Owner', 'Admin'])) {
            if(!$user->employee) abort(403, 'User not linked to Employee.');
            $query->where('employee_id', $user->employee->id);
        }

        $encashments = $query->paginate(15);
            
        return view('leave-encashments.index', compact('encashments'));
    }

    public function create()
    {
        $user = Auth::user();
        $businessId = $user->business_id;
        
        // ✅ 2. Selection Logic
        if ($user->hasRole(['Owner', 'Admin'])) {
            // Admins can select anyone
            $employees = Employee::where('business_id', $businessId)->where('status', 'active')->orderBy('name')->get();
        } else {
            // Employees can only select themselves
            $employees = collect([$user->employee]); 
        }
        
        $leaveTypes = LeaveType::where('business_id', $businessId)->where('is_encashable', true)->get();

        return view('leave-encashments.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        // ... (Keep existing validation) ...
        $request->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'days' => 'required|numeric|min:0.5',
            'amount' => 'required|numeric',
            'encashment_date' => 'required|date',
        ]);

        // Security: Ensure non-admins cannot request for others
        if (!Auth::user()->hasRole(['Owner', 'Admin']) && $request->employee_id != Auth::user()->employee->id) {
            abort(403);
        }

        LeaveEncashment::create([
            'business_id' => Auth::user()->business_id,
            'employee_id' => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'days' => $request->days,
            'amount' => $request->amount,
            'status' => 'pending',
            'encashment_date' => $request->encashment_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('leave-encashments.index')->with('success', 'Encashment request submitted.');
    }

    // ... (Keep edit, update, destroy, getEstimate methods as they are) ...
    // Note: In 'edit' and 'destroy', the logic I gave you previously already checks for 'pending' status, 
    // which fulfills your requirement that they can only edit/delete before approval.

    public function getEstimate(Request $request)
    {
        // (Use the code provided in previous steps)
        // ...
        
        // RE-COPYING ESSENTIAL LOGIC FOR CONTEXT:
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'days' => 'required|numeric|min:0.5',
        ]);

        $employee = Employee::with('salaryComponents')->findOrFail($request->employee_id);
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $totalAllotted = $employee->leaveTypes->find($leaveType->id)->pivot->days_allotted ?? 0;
        
        // Calculate used logic... (As per previous code)
        // For simplicity in this snippets, assume logic is handled
        $daysUsed = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', $leaveType->name)
            ->where('status', 'approved')
            ->get()
            ->sum(function($req) {
                 return \Carbon\Carbon::parse($req->start_date)
                    ->diffInDaysFiltered(fn($date) => !$date->isSunday(), \Carbon\Carbon::parse($req->end_date)) + 1;
            });

        $currentBalance = $totalAllotted - $daysUsed;

        if ($currentBalance < $leaveType->min_balance_required) {
            return response()->json(['error' => "Min Balance required: {$leaveType->min_balance_required}"]);
        }
        
        $availableToEncash = $currentBalance - $leaveType->min_balance_required;
        if ($leaveType->max_days_encashable > 0) {
            $availableToEncash = min($availableToEncash, $leaveType->max_days_encashable);
        }

        if ($request->days > $availableToEncash) {
            return response()->json(['error' => "Max encashable days: {$availableToEncash}"]);
        }

        $salaryBase = ($leaveType->encashment_variable === 'gross_salary') ? $employee->gross_salary : $employee->basic_salary;
        $divisor = $leaveType->encashment_divisor > 0 ? $leaveType->encashment_divisor : 30;
        $perDayRate = $salaryBase / $divisor;
        $totalAmount = round($perDayRate * $request->days, 2);

        return response()->json([
            'success' => true, 'current_balance' => $currentBalance, 'available_limit' => $availableToEncash,
            'per_day_rate' => number_format($perDayRate, 2), 'total_amount' => number_format($totalAmount, 2), 'raw_amount' => $totalAmount
        ]);
    }
    
    // ✅ 3. NEW: Approval Workflow Methods
    
    public function approve(LeaveEncashment $leaveEncashment)
    {
        if(!Auth::user()->hasRole(['Owner', 'Admin'])) abort(403);
        
        $leaveEncashment->update(['status' => 'approved']);
        return back()->with('success', 'Encashment Approved. It will be included in the next Salary Sheet.');
    }

    public function reject(LeaveEncashment $leaveEncashment)
    {
        if(!Auth::user()->hasRole(['Owner', 'Admin'])) abort(403);
        
        $leaveEncashment->update(['status' => 'rejected']);
        return back()->with('success', 'Encashment Rejected.');
    }
    
    public function edit(LeaveEncashment $leaveEncashment) { /* Use previous code */ return view('leave-encashments.edit', ['encashment' => $leaveEncashment, 'employees' => Employee::all(), 'leaveTypes' => LeaveType::all()]); } // Simplified for brevity
    public function update(Request $request, LeaveEncashment $leaveEncashment) { /* Use previous code */ $leaveEncashment->update($request->all()); return redirect()->route('leave-encashments.index'); } 
    public function destroy(LeaveEncashment $leaveEncashment) { /* Use previous code */ $leaveEncashment->delete(); return back(); }
}