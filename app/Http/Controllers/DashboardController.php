<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SalarySheet;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. ADMIN / OWNER DASHBOARD
        if ($user->hasRole(['Owner', 'Admin'])) {
            return $this->adminDashboard($user->business_id);
        }

        // 2. EMPLOYEE DASHBOARD
        if ($user->employee) {
            return $this->employeeDashboard($user->employee);
        }

        // 3. CLIENT DASHBOARD (New Addition)
        // If the user has the 'Client' role, send them to the specific portal route
        if ($user->hasRole('Client')) {
            // This redirects to the route named 'client.dashboard' defined in your web.php
            return redirect()->route('client.dashboard');
        }

        // Fallback for users with no role and no employee link
        return view('welcome'); 
    }

    /**
     * Logic for Admin Dashboard (Company Wide Stats)
     */
    private function adminDashboard($businessId)
    {
        // 1. Employee Stats
        $activeEmployees = Employee::where('business_id', $businessId)->where('status', 'active')->count();
        
        // 2. Financial Overview
        $monthlyPayrollCost = Employee::where('business_id', $businessId)
                                ->where('status', 'active')
                                ->sum('gross_salary');

        // 3. Organization
        $departmentsCount = Department::where('business_id', $businessId)->count();
        
        // 4. Attendance Snapshot
        $today = Carbon::today();
        $attendanceQuery = Attendance::whereHas('employee', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })->whereDate('date', $today);
        
        $presentCount = (clone $attendanceQuery)->whereIn('status', ['present', 'late', 'half-day'])->count();
        $lateCount = (clone $attendanceQuery)->where('status', 'late')->count();
        $leaveCount = (clone $attendanceQuery)->where('status', 'leave')->count();
        $absentCount = (clone $attendanceQuery)->where('status', 'absent')->count();
        
        $notMarkedCount = max(0, $activeEmployees - ($presentCount + $leaveCount + $absentCount));

        // 5. Recent Joinings
        $recentJoinings = Employee::where('business_id', $businessId)
                            ->orderBy('joining_date', 'desc')
                            ->take(5)
                            ->get();

        // 6. Data Health
        $missingSalaryCount = Employee::where('business_id', $businessId)
                                ->where('status', 'active')
                                ->where('gross_salary', '<=', 0)
                                ->count();

        return view('dashboard.admin', compact(
            'activeEmployees', 'monthlyPayrollCost', 'departmentsCount', 
            'presentCount', 'lateCount', 'leaveCount', 'absentCount', 
            'notMarkedCount', 'recentJoinings', 'missingSalaryCount'
        ));
    }

    /**
     * Logic for Employee Dashboard (Personal Stats)
     */
    private function employeeDashboard($employee)
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. My Attendance (Current Month)
        $attendanceStats = [
            'present' => $employee->attendances()->whereBetween('date', [$startOfMonth, $endOfMonth])->whereIn('status', ['present', 'late', 'half-day'])->count(),
            'late' => $employee->attendances()->whereBetween('date', [$startOfMonth, $endOfMonth])->where('status', 'late')->count(),
            'absent' => $employee->attendances()->whereBetween('date', [$startOfMonth, $endOfMonth])->where('status', 'absent')->count(),
            'today_status' => $employee->attendances()->where('date', $today)->first(),
        ];

        // 2. My Leave Balances
        $leaveBalances = [];
        $employee->load('leaveTypes'); // Ensure relationship is loaded
        
        foreach ($employee->leaveTypes as $type) {
            // Calculate used leaves for this type in current year
            $used = $employee->leaveRequests()
                ->where('leave_type', $type->name)
                ->where('status', 'approved')
                ->whereYear('start_date', Carbon::now()->year)
                ->get()
                ->sum(function ($req) {
                    return Carbon::parse($req->start_date)->diffInDaysFiltered(fn($d) => !$d->isSunday(), Carbon::parse($req->end_date)) + 1;
                });

            $leaveBalances[] = [
                'name' => $type->name,
                'total' => $type->pivot->days_allotted,
                'used' => $used,
                'remaining' => max(0, $type->pivot->days_allotted - $used)
            ];
        }

        // 3. My Last Salary
        $lastSalary = $employee->salarySheetItems()
            ->whereHas('salarySheet', fn($q) => $q->where('status', 'finalized'))
            ->latest()
            ->first();

        // 4. My Loans
        $loanBalance = Loan::where('employee_id', $employee->id)
            ->where('status', 'running')
            ->get()
            ->sum(fn($l) => $l->total_amount - $l->recovered_amount);
        
        // 5. Recent Requests
        $recentRequests = $employee->leaveRequests()->latest()->take(3)->get();

        return view('dashboard.employee', compact('employee', 'attendanceStats', 'leaveBalances', 'lastSalary', 'loanBalance', 'recentRequests'));
    }
}