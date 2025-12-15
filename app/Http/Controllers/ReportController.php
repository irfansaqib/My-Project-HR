<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\SalarySheet;
use App\Models\SalarySheetItem;
use App\Models\Loan;
use App\Models\Fund;
use App\Models\FundContribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportController extends Controller
{
    // --------------------------------------------------------------------------------------------------
    // ATTENDANCE & LEAVE REPORTS
    // --------------------------------------------------------------------------------------------------
    
    public function attendanceReport(Request $request)
    {
        $business = Auth::user()->business;
        $employees = $business->employees()->orderBy('name')->get();
        $shifts = $business->shifts()->orderBy('name')->get();
        $query = Attendance::whereHas('employee', fn($q) => $q->where('business_id', $business->id))->with('employee');
        
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('shift_id')) $query->whereHas('employee.shiftAssignments', fn($q) => $q->where('shift_id', $request->shift_id));
        if ($request->filled('date_from')) $query->where('date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('date', '<=', $request->date_to);

        $attendances = $query->orderBy('date', 'desc')->get();
        
        return view('reports.attendance', compact('attendances', 'employees', 'shifts'));
    }

    public function attendanceCalendar(Request $request)
    {
        $employees = Auth::user()->business->employees()->orderBy('name')->get();
        return view('reports.calendar', compact('employees'));
    }

    public function calendarEvents(Request $request)
    {
        if (!$request->filled('employee_id')) return response()->json([]);

        $business = Auth::user()->business;
        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();
        $today = Carbon::today();

        $holidays = $business->holidays()->whereBetween('date', [$start, $end])->get()->keyBy(fn($h) => $h->date->format('Y-m-d'));
        $employee = $business->employees()->where('id', $request->employee_id)
            ->with(['shiftAssignments.shift', 'attendances' => fn($q) => $q->whereBetween('date', [$start, $end]), 'leaveRequests' => fn($q) => $q->where('status', 'approved')->where('start_date', '<=', $end)->where('end_date', '>=', $start)])
            ->firstOrFail();
            
        $events = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $dayName = $date->format('l');

            $attendance = $employee->attendances->first(fn($att) => $att->date->isSameDay($date));
            $approvedLeave = $employee->leaveRequests->first(fn($l) => $date->betweenOrEquals($l->start_date, $l->end_date));
            $shiftAssignment = $employee->shiftAssignments->first(fn($a) => $date->between($a->start_date, $a->end_date ?? now()->addYear()));
            $weeklyOffDays = ($shiftAssignment && $shiftAssignment->shift) ? explode(',', $shiftAssignment->shift->weekly_off) : [];

            if ($holidays->has($dateString)) {
                $holiday = $holidays->get($dateString);
                $events[] = ['start' => $dateString, 'allDay' => true, 'display' => 'background', 'color' => '#cfe2ff'];
                $events[] = ['title' => "HOLIDAY: {$holiday->title}", 'start' => $dateString, 'allDay' => true, 'className' => 'fc-event-holiday-text'];
            } elseif (in_array($dayName, $weeklyOffDays)) {
                $events[] = ['start' => $dateString, 'allDay' => true, 'display' => 'background', 'color' => '#d1e7dd'];
                $events[] = ['title' => 'OFF', 'start' => $dateString, 'allDay' => true, 'className' => 'fc-event-offday-text'];
            } elseif ($approvedLeave) {
                $events[] = ['title' => 'Leave', 'start' => $dateString, 'allDay' => true, 'backgroundColor' => '#0d6efd', 'borderColor' => '#0d6efd'];
            } elseif ($attendance) {
                $title = ucfirst($attendance->status);
                $color = '#198754';
                if(in_array($attendance->status, ['present', 'late', 'half-day'])) {
                    $in = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : '-';
                    $out = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : '-';
                    $title = "$in - $out";
                    if($attendance->status == 'late') $color = '#fd7e14';
                }
                $events[] = ['title' => $title, 'start' => $dateString, 'allDay' => true, 'backgroundColor' => $color, 'borderColor' => $color];
            } elseif ($date->lte($today)) {
                 $events[] = ['title' => 'Absent', 'start' => $dateString, 'allDay' => true, 'backgroundColor' => '#dc3545', 'borderColor' => '#dc3545'];
            }
        }
        
        return response()->json($events);
    }
    
    public function leaveReport(Request $request)
    {
        $businessId = Auth::user()->business_id;
        
        // 1. Fetch Dropdown Data
        $employees = Employee::where('business_id', $businessId)->orderBy('name')->get();
        $leaveTypes = \App\Models\LeaveType::where('business_id', $businessId)->get();

        // 2. Build Query
        $query = LeaveRequest::with(['employee', 'leaveType'])
            ->whereHas('employee', function($q) use ($businessId) {
                $q->where('business_id', $businessId);
            });

        // 3. Apply Filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->where('start_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('end_date', '<=', $request->to_date);
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();

        // 4. Calculate Stats for Cards
        $totalRequests = $leaves->count();
        $approvedCount = $leaves->where('status', 'approved')->count();
        $pendingCount = $leaves->where('status', 'pending')->count();
        $rejectedCount = $leaves->where('status', 'rejected')->count();

        // 5. Handle Excel Export
        if ($request->export === 'excel') {
            return $this->exportLeavesToCsv($leaves);
        }

        return view('reports.leave', compact(
            'leaves', 'employees', 'leaveTypes', 
            'totalRequests', 'approvedCount', 'pendingCount', 'rejectedCount'
        ));
    }

    /**
     * Helper to Export Leave Report to CSV
     */
    private function exportLeavesToCsv($leaves)
    {
        $filename = "Leave_Report_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use($leaves) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Reason', 'Status', 'Applied On']);
            
            foreach ($leaves as $leave) {
                fputcsv($file, [
                    $leave->employee->name,
                    $leave->leaveType->name ?? 'N/A',
                    $leave->start_date->format('d M, Y'),
                    $leave->end_date->format('d M, Y'),
                    $leave->days,
                    $leave->reason,
                    ucfirst($leave->status),
                    $leave->created_at->format('d M, Y')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --------------------------------------------------------------------------------------------------
    // PAYROLL REPORT (UPDATED TO FIX ZERO VALUES)
    // --------------------------------------------------------------------------------------------------

    public function payrollReport(Request $request)
    {
        $businessId = Auth::user()->business_id;
        
        // Defaults: Current Year
        $fromMonth = $request->from_month ? Carbon::parse($request->from_month)->startOfMonth() : Carbon::now()->startOfYear();
        $toMonth = $request->to_month ? Carbon::parse($request->to_month)->endOfMonth() : Carbon::now()->endOfMonth();

        // Query finalized salary sheets within range
        $query = SalarySheet::where('business_id', $businessId)
            ->where('status', 'finalized') // Only show finalized data
            ->whereBetween('month', [$fromMonth, $toMonth]);

        // ✅ FIX: Added with('items') to load the children rows so we can calculate sums
        $sheets = $query->with('items')->withCount('items')->orderBy('month', 'desc')->get();

        // ✅ FIX: Loop through sheets and calculate totals manually if DB returns 0
        foreach ($sheets as $sheet) {
            // If the main sheet has 0 values but has employees (items), we calculate from items
            if ($sheet->total_net_salary <= 0 && $sheet->items->count() > 0) {
                
                // Sum up the columns from the individual employee items
                $sumGross = $sheet->items->sum('gross_salary');
                $sumTax = $sheet->items->sum('income_tax');
                $sumNet = $sheet->items->sum('net_salary');
                
                // Update the sheet object temporarily for display
                $sheet->total_gross_salary = $sumGross;
                $sheet->total_tax = $sumTax;
                $sheet->total_net_salary = $sumNet;
                
                // Calculate deductions (Gross - Net)
                $sheet->total_deductions = $sumGross - $sumNet;
            }
        }

        // Calculate Totals for the "Cards" at the top based on the corrected values
        $totalGross = $sheets->sum('total_gross_salary');
        $totalNet = $sheets->sum('total_net_salary');
        $totalTax = $sheets->sum('total_tax');
        $totalDeductions = $sheets->sum('total_deductions');

        // Excel Export
        if ($request->export === 'excel') {
            return $this->exportPayrollSummaryCsv($sheets);
        }

        return view('reports.payroll', compact(
            'sheets', 'totalGross', 'totalNet', 'totalTax', 'totalDeductions', 'fromMonth', 'toMonth'
        ));
    }

    /**
     * Helper to Export Payroll Summary
     */
    private function exportPayrollSummaryCsv($sheets)
    {
        $filename = "Payroll_Summary_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use($sheets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Month', 'Total Employees', 'Total Gross Pay', 'Total Tax', 'Total Deductions', 'Total Net Pay']);
            
            foreach ($sheets as $sheet) {
                fputcsv($file, [
                    $sheet->month->format('F Y'),
                    $sheet->items_count ?? $sheet->items->count(), 
                    $sheet->total_gross_salary,
                    $sheet->total_tax,
                    $sheet->total_deductions,
                    $sheet->total_net_salary
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --------------------------------------------------------------------------------------------------
    // LOANS & ADVANCES REPORT
    // --------------------------------------------------------------------------------------------------
    
    public function loanReport(Request $request)
    {
        $businessId = Auth::user()->business_id;
        $employees = Employee::where('business_id', $businessId)->orderBy('name')->get();

        // Filters
        $employeeId = $request->employee_id;
        $type = $request->type; 
        $status = $request->status;
        $asAtDate = $request->as_at_date ? Carbon::parse($request->as_at_date)->endOfDay() : now()->endOfDay();

        $query = Loan::with(['employee', 'fund'])->where('business_id', $businessId);

        if ($employeeId) $query->where('employee_id', $employeeId);

        if ($type) {
            if ($type === 'fund_loan') {
                $query->whereNotNull('fund_id');
            } elseif ($type === 'loan') {
                $query->where('type', 'loan')->whereNull('fund_id');
            } else {
                $query->where('type', $type);
            }
        }
        
        $query->where('loan_date', '<=', $asAtDate);

        $loans = $query->orderBy('loan_date', 'desc')->get();

        // Process "As At" Balances
        $reportData = $loans->map(function($loan) use ($asAtDate) {
            $recoveredUntilDate = $loan->repayments()
                ->where('payment_date', '<=', $asAtDate)
                ->sum('amount');
            
            $balance = $loan->total_amount - $recoveredUntilDate;
            $historicalStatus = ($balance <= 0) ? 'Completed' : 'Running';
            
            // Determine Label
            $typeLabel = ucfirst($loan->type);
            if ($loan->fund_id) {
                $typeLabel = 'Fund Loan';
            }

            return (object) [
                'date' => $loan->loan_date->format('d M, Y'),
                'employee_name' => $loan->employee->name,
                'type' => $typeLabel,
                'fund_name' => $loan->fund->name ?? null,
                'total_amount' => $loan->total_amount,
                'recovered' => $recoveredUntilDate,
                'balance' => $balance,
                'status' => $historicalStatus,
            ];
        });

        if ($status) {
            $reportData = $reportData->filter(fn($row) => strtolower($row->status) === strtolower($status));
        }

        if ($request->export === 'excel') {
            return $this->exportLoansToCsv($reportData, $asAtDate);
        }

        return view('reports.loans', compact('reportData', 'employees', 'asAtDate'));
    }

    private function exportLoansToCsv($data, $date)
    {
        $filename = "Loan_Report_As_At_" . $date->format('Y-m-d') . ".csv";
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$filename", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0" ];

        $callback = function() use($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Employee', 'Type', 'Fund (If Applicable)', 'Total Amount', 'Recovered', 'Balance', 'Status']);
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date,
                    $row->employee_name,
                    $row->type,
                    $row->fund_name ?? '-',
                    $row->total_amount,
                    $row->recovered,
                    $row->balance,
                    $row->status
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // --------------------------------------------------------------------------------------------------
    // CONTRIBUTORY FUNDS REPORT
    // --------------------------------------------------------------------------------------------------
    
    public function fundReport(Request $request)
    {
        $businessId = Auth::user()->business_id;
        $employees = Employee::where('business_id', $businessId)->orderBy('name')->get();
        $funds = Fund::where('business_id', $businessId)->get();

        $employeeId = $request->employee_id;
        $fundId = $request->fund_id;
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : Carbon::now()->startOfYear();
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : Carbon::now()->endOfDay();

        $query = FundContribution::with(['employee', 'fund'])
            ->whereHas('fund', function($q) use ($businessId) {
                $q->where('business_id', $businessId);
            })
            ->whereBetween('transaction_date', [$fromDate, $toDate]);

        if ($employeeId) $query->where('employee_id', $employeeId);
        if ($fundId) $query->where('fund_id', $fundId);

        $contributions = $query->orderBy('transaction_date', 'desc')->get();

        $totalEmployeeShare = $contributions->where('type', 'employee_share')->sum('amount');
        $totalEmployerShare = $contributions->where('type', 'employer_share')->sum('amount');
        $totalProfit = $contributions->where('type', 'profit_credit')->sum('amount');
        $totalWithdrawals = $contributions->where('type', 'withdrawal')->sum('amount');
        $totalFundValue = ($totalEmployeeShare + $totalEmployerShare + $totalProfit) - $totalWithdrawals;

        if ($request->export === 'excel') {
            return $this->exportFundsToCsv($contributions);
        }

        return view('reports.funds', compact(
            'contributions', 'employees', 'funds', 'fromDate', 'toDate',
            'totalEmployeeShare', 'totalEmployerShare', 'totalProfit', 'totalWithdrawals', 'totalFundValue'
        ));
    }

    private function exportFundsToCsv($data)
    {
        $filename = "Fund_Report_" . now()->format('Y-m-d') . ".csv";
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$filename", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0" ];
        $callback = function() use($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Employee', 'Fund Name', 'Type', 'Amount', 'Description']);
            foreach ($data as $row) {
                $amount = ($row->type == 'withdrawal') ? -1 * $row->amount : $row->amount;
                fputcsv($file, [$row->transaction_date->format('d M, Y'), $row->employee->name, $row->fund->name, ucfirst(str_replace('_', ' ', $row->type)), $amount, $row->description]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // --------------------------------------------------------------------------------------------------
    // TAX DEDUCTION REPORT
    // --------------------------------------------------------------------------------------------------

    /**
     * Tax Deduction Report (With Excel Export)
     */
    public function taxDeductionReport(Request $request)
    {
        $businessId = Auth::user()->business_id;
        $business = Auth::user()->business; // Load business for legal name
        
        // Determine Date Range
        $fromDate = $request->from_month ? Carbon::parse($request->from_month)->startOfMonth() : Carbon::now()->startOfYear();
        $toDate = $request->to_month ? Carbon::parse($request->to_month)->endOfMonth() : Carbon::now()->endOfMonth();

        // Fetch Salary Sheet Items with Tax > 0
        $query = SalarySheetItem::whereHas('salarySheet', function($q) use ($businessId, $fromDate, $toDate) {
                $q->where('business_id', $businessId)
                  ->where('status', 'finalized')
                  ->whereBetween('month', [$fromDate, $toDate]);
            })
            ->with(['employee', 'salarySheet'])
            ->where('income_tax', '>', 0);

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        $records = $query->get();
        $employees = Employee::where('business_id', $businessId)->orderBy('name')->get();

        // Calculate Totals
        $totalTax = $records->sum('income_tax');
        $totalGross = $records->sum('gross_salary');

        // EXPORT TO EXCEL Check
        if ($request->export === 'excel') {
            return $this->exportTaxDeductionCsv($records, $business, $fromDate, $toDate);
        }

        return view('reports.tax_deduction', compact('records', 'employees', 'fromDate', 'toDate', 'totalTax', 'totalGross'));
    }

    /**
     * Helper to Generate Tax Excel/CSV
     */
    private function exportTaxDeductionCsv($records, $business, $start, $end)
    {
        $filename = "Tax_Deduction_Report_" . $start->format('M_Y') . "_to_" . $end->format('M_Y') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Period Label logic
        if ($start->format('Y-m') === $end->format('Y-m')) {
            $periodLabel = "For the Month of " . $start->format('F, Y');
        } else {
            $periodLabel = "For the Period " . $start->format('F, Y') . " to " . $end->format('F, Y');
        }

        $callback = function() use($records, $business, $periodLabel) {
            $file = fopen('php://output', 'w');
            
            // 1. Business Legal Name
            fputcsv($file, [$business->legal_name ?? $business->name]);
            
            // 2. Report Title
            fputcsv($file, ['Tax Deduction Detail']);
            
            // 3. Month/Period
            fputcsv($file, [$periodLabel]);
            
            // Spacer
            fputcsv($file, []); 

            // 4. Column Headers
            fputcsv($file, [
                'Payment Section', 
                'Employee NTN', 
                'Employee CNIC', 
                'Employee Name', 
                'Employee City', 
                'Employee Address', 
                'Employee Status', 
                'Gross Salary', 
                'Tax Deducted'
            ]);

            // 5. Data Rows
            foreach ($records as $row) {
                fputcsv($file, [
                    '149(1)', // Hardcoded Section
                    $row->employee->ntn ?? '', // Assumes 'ntn' column exists, else empty
                    $row->employee->cnic,
                    $row->employee->name,
                    $row->employee->city ?? '', // Assumes 'city' column exists, else empty
                    $row->employee->address ?? '',
                    'Individual', // Hardcoded Status
                    $row->gross_salary,
                    $row->income_tax
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}