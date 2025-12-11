<?php

namespace App\Http\Controllers;

use App\Helpers\NumberHelper;
use App\Models\Business;
use App\Models\Employee;
use App\Models\SalarySheet;
use App\Models\SalarySheetItem;
use App\Models\SalaryComponent;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\FundContribution;
use App\Models\LeaveEncashment;
use App\Models\Fund;
use App\Models\Designation;
use App\Models\Department;
use App\Models\BusinessBankAccount;
use App\Services\SalaryCalculationService;
use App\Services\TaxCalculatorService;
use App\Services\MailConfigurationService;
use App\Mail\PayslipEmail;
use App\Mail\TaxCertificateEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaryController extends Controller
{
    protected $taxCalculator;
    protected $salaryCalculator;

    public function __construct(TaxCalculatorService $taxCalculator, SalaryCalculationService $salaryCalculator)
    {
        $this->taxCalculator = $taxCalculator;
        $this->salaryCalculator = $salaryCalculator;
    }

    // =============================================================================================
    // 1. SALARY SHEET MANAGEMENT (Admin)
    // =============================================================================================

    public function index()
    {
        $businessId = Auth::user()->business_id;
        $salarySheets = SalarySheet::where('business_id', $businessId)
            ->withCount('items')
            ->orderBy('month', 'desc')
            ->paginate(10);

        return view('salary.index', compact('salarySheets'));
    }

    public function create()
    {
        return view('salary.create');
    }

    /**
     * Generate Salary Sheet (Draft Mode)
     * Includes Sequential Validation, Arrears Calculation, and Fund/Loan Logic.
     */
    public function generate(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);
        $businessId = Auth::user()->business_id;
        $month = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        $payrollMonthEnd = $month->copy()->endOfMonth();

        // 1. Check for Duplicate
        $existingSheet = SalarySheet::where('business_id', $businessId)
                                    ->whereYear('month', $month->year)
                                    ->whereMonth('month', $month->month)
                                    ->first();
        if ($existingSheet) {
            return redirect()->route('salaries.index')->with('error', 'A salary sheet for ' . $month->format('F, Y') . ' already exists.');
        }

        // 2. SEQUENCE CHECK: Strict Gap Detection
        $lastSheet = SalarySheet::where('business_id', $businessId)
            ->orderBy('month', 'desc')
            ->first();

        if ($lastSheet) {
            if ($lastSheet->status !== 'finalized') {
                return redirect()->route('salaries.index')->with('error', 'Previous salary sheet (' . $lastSheet->month->format('F, Y') . ') must be FINALIZED first.');
            }
            
            $nextExpectedMonth = $lastSheet->month->copy()->addMonth()->startOfMonth();
            if ($month->gt($nextExpectedMonth)) {
                return redirect()->route('salaries.index')->with('error', 'Sequence Error! You cannot skip months. Generate ' . $nextExpectedMonth->format('F, Y') . ' first.');
            }
        }
        
        // 3. Fetch Eligible Employees
        $employees = Employee::where('business_id', $businessId)
            ->where('status', 'active')
            ->where('joining_date', '<=', $payrollMonthEnd)
            ->with(['salaryComponents', 'incentives'])
            ->get();

        if ($employees->isEmpty()) {
            return redirect()->route('salaries.create')->with('error', 'No active employees found.');
        }

        // 4. Check Data Integrity
        $problematicEmployees = [];
        foreach ($employees as $employee) {
            if (is_null($employee->basic_salary)) {
                $problematicEmployees[] = $employee->name;
            }
        }
        if (!empty($problematicEmployees)) {
            return redirect()->route('salaries.create')->with('error', 'Missing Basic Salary for: ' . implode(', ', $problematicEmployees));
        }

        DB::beginTransaction();
        try {
            $sheet = SalarySheet::create([
                'business_id' => $businessId,
                'month' => $month->toDateString(),
                'status' => 'generated' // Draft mode
            ]);

            foreach ($employees as $employee) {
                // Calculate Breakdown
                $salaryData = $this->salaryCalculator->calculateForMonth($employee, $month);
                $incomeTax = $this->taxCalculator->calculate($employee, $month);
                
                $netSalary = $salaryData['gross_salary'] 
                           - $salaryData['total_deductions_components'] 
                           - $incomeTax;

                $arrears = $salaryData['arrears_adjustment'] ?? 0;
                $payable = $netSalary + $arrears;

                $sheetItem = SalarySheetItem::create([
                    'salary_sheet_id' => $sheet->id,
                    'employee_id' => $employee->id,
                    'gross_salary' => $salaryData['gross_salary'],
                    'bonus' => $salaryData['bonus'],
                    'leave_encashment_amount' => $salaryData['leave_encashment_amount'],
                    'deductions' => $salaryData['total_deductions_components'], 
                    'income_tax' => $incomeTax,
                    'net_salary' => $netSalary,
                    'arrears_adjustment' => $arrears, 
                    'payable_amount' => $payable,     
                    'paid_amount' => 0,               
                    'payment_status' => 'unpaid',
                    'status' => 'pending',
                ]);

                // Process Encashments (Mark as Paid)
                if (!empty($salaryData['encashment_ids'])) {
                    LeaveEncashment::whereIn('id', $salaryData['encashment_ids'])->update([
                        'status' => 'paid',
                        'salary_sheet_item_id' => $sheetItem->id,
                        'updated_at' => now(),
                    ]);
                }

                // Record Loan Repayments
                $activeLoans = Loan::where('employee_id', $employee->id)
                    ->where('status', 'running')
                    ->where('repayment_start_date', '<=', $payrollMonthEnd)
                    ->get();

                foreach($activeLoans as $loan) {
                    $pending = $loan->total_amount - $loan->recovered_amount;
                    if ($pending > 0) {
                        $deduct = ($loan->type === 'advance') ? $pending : min($loan->installment_amount, $pending);
                        if ($deduct > 0) {
                            LoanRepayment::create([
                                'loan_id' => $loan->id,
                                'salary_sheet_item_id' => $sheetItem->id,
                                'amount' => $deduct,
                                'payment_date' => now(),
                            ]);
                        }
                    }
                }

                // Record Fund Contributions
                if (!empty($salaryData['fund_contributions'])) {
                    foreach ($salaryData['fund_contributions'] as $fundData) {
                        $contributionDate = $month->copy()->endOfMonth(); 
                        if ($fundData['employee_share'] > 0) {
                            FundContribution::create([
                                'fund_id' => $fundData['fund_id'],
                                'employee_id' => $employee->id,
                                'salary_sheet_item_id' => $sheetItem->id,
                                'type' => 'employee_share',
                                'amount' => $fundData['employee_share'],
                                'transaction_date' => $contributionDate,
                                'description' => 'Salary Deduction: ' . $month->format('M Y')
                            ]);
                        }
                        if ($fundData['employer_share'] > 0) {
                            FundContribution::create([
                                'fund_id' => $fundData['fund_id'],
                                'employee_id' => $employee->id,
                                'salary_sheet_item_id' => $sheetItem->id,
                                'type' => 'employer_share',
                                'amount' => $fundData['employer_share'],
                                'transaction_date' => $contributionDate,
                                'description' => 'Employer Contribution: ' . $month->format('M Y')
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('salaries.show', $sheet->id)->with('success', 'Salary Sheet generated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('salaries.create')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display Salary Sheet (Review Mode)
     * Filters empty columns to keep the view clean.
     */
    public function show(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        
        $salarySheet->load(['items.employee.designationRelation', 'items.employee.payingBankAccount', 'business']);
        
        foreach ($salarySheet->items as $item) {
            $calculatedData = $this->salaryCalculator->calculateForMonth($item->employee, $salarySheet->month);
            $item->allowances_breakdown = $calculatedData['allowances_breakdown'];
            $item->deductions_breakdown = $calculatedData['deductions_breakdown'];
        }

        // Dynamic Flags
        $hasEncashment = $salarySheet->items->sum('leave_encashment_amount') > 0;
        $hasArrears = $salarySheet->items->sum('arrears_adjustment') > 0;
        $hasBonus = $salarySheet->items->sum('bonus') > 0;

        // Filter Active Allowances
        $allowanceHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)
            ->where('type', 'allowance')->orderBy('name')->pluck('name');
        $activeAllowances = [];
        foreach ($allowanceHeaders as $header) {
            if ($salarySheet->items->sum(fn($i) => $i->allowances_breakdown[$header] ?? 0) > 0) 
                $activeAllowances[] = $header;
        }

        // Filter Active Deductions
        $deductionHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)
            ->where('type', 'deduction')->orderBy('name')->pluck('name');
        $activeDeductions = [];
        foreach ($deductionHeaders as $header) {
            if (in_array(strtolower($header), ['income tax', 'tax', 'incometax'])) continue;
            if ($salarySheet->items->sum(fn($i) => $i->deductions_breakdown[$header] ?? 0) > 0)
                $activeDeductions[] = $header;
        }

        // Paying Banks (For Export Dropdown)
        $payingBanks = $salarySheet->items->map(function ($item) {
            return $item->employee->payingBankAccount;
        })->filter()->unique('id')->values();
        
        return view('salary.show', [
            'salarySheet' => $salarySheet,
            'monthName' => $salarySheet->month->format('F, Y'),
            'activeAllowances' => $activeAllowances,
            'activeDeductions' => $activeDeductions,
            'business' => $salarySheet->business,
            'hasEncashment' => $hasEncashment,
            'hasArrears' => $hasArrears,
            'hasBonus' => $hasBonus,
            'payingBanks' => $payingBanks
        ]);
    }

    /**
     * Bulk Finalize & Save
     */
    public function finalize(Request $request, SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        if ($salarySheet->status === 'finalized') {
            return back()->with('error', 'This sheet is already finalized.');
        }

        $items = $request->input('items', []);

        DB::beginTransaction();
        try {
            foreach ($items as $itemId => $data) {
                $item = SalarySheetItem::find($itemId);
                if (!$item || $item->salary_sheet_id !== $salarySheet->id) continue;

                $newTax = (float) ($data['income_tax'] ?? $item->income_tax);
                if ($newTax != $item->income_tax) {
                    $newNet = $item->gross_salary - $item->deductions - $newTax;
                    $newPayable = $newNet + $item->arrears_adjustment;
                    
                    $item->income_tax = $newTax;
                    $item->is_tax_manual = true;
                    $item->net_salary = $newNet;
                    $item->payable_amount = $newPayable;
                }

                $isHeld = isset($data['is_held']) && $data['is_held'] == '1';
                $amountPaying = (float) ($data['paid_amount'] ?? 0);

                if ($isHeld) {
                    $item->payment_status = 'held';
                    $item->paid_amount = 0; 
                } else {
                    $item->paid_amount = $amountPaying;
                    if ($amountPaying <= 0) $item->payment_status = 'unpaid';
                    elseif ($amountPaying >= $item->payable_amount) $item->payment_status = 'paid';
                    else $item->payment_status = 'partial';
                }
                $item->save();
            }

            $salarySheet->update(['status' => 'finalized']);
            DB::commit();
            return redirect()->route('salaries.show', $salarySheet->id)->with('success', 'Salary Sheet finalized.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * AJAX Update Single Item
     */
    public function updateItem(Request $request, SalarySheetItem $item)
    {
        if ($request->has('income_tax')) {
            $newTax = (float) $request->income_tax;
            $newNet = $item->gross_salary - $item->deductions - $newTax;
            $newPayable = $newNet + $item->arrears_adjustment;

            $item->update([
                'income_tax' => $newTax,
                'is_tax_manual' => true,
                'net_salary' => $newNet,
                'payable_amount' => $newPayable
            ]);
        }

        if ($request->has('payment_action')) {
            if ($request->payment_action === 'hold') {
                $item->update(['payment_status' => 'held']);
            } elseif ($request->payment_action === 'release') {
                $item->update(['payment_status' => 'unpaid']);
            } elseif ($request->payment_action === 'pay_partial') {
                $amountToPay = (float) $request->paid_amount;
                $status = ($amountToPay >= $item->payable_amount) ? 'paid' : 'partial';
                $item->update(['paid_amount' => $amountToPay, 'payment_status' => $status]);
            }
        }
        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    /**
     * Export Bank Transfer List (Filtered by Paying Bank)
     */
    public function exportBankTransfer(Request $request, SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }

        $bankAccountId = $request->query('account_id'); 
        $salarySheet->load(['items.employee.payingBankAccount']);
        $business = Business::find(Auth::user()->business_id);
        
        if ($bankAccountId) {
            $selectedBank = BusinessBankAccount::find($bankAccountId);
            $bankName = $selectedBank->bank_name;
            $accountNum = $selectedBank->account_number;
            $fileLabel = str_replace(' ', '_', $selectedBank->bank_name);
        } else {
            $bankName = "All Banks Combined";
            $accountNum = "-";
            $fileLabel = "All_Accounts";
        }

        $fileName = 'Salary_Transfer_' . $fileLabel . '_' . $salarySheet->month->format('M_Y') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $companyName = $business->legal_name ?? $business->name;
        $monthLabel = 'Salary Month: ' . $salarySheet->month->format('F, Y');

        $callback = function() use($salarySheet, $companyName, $bankName, $accountNum, $monthLabel, $bankAccountId) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [$companyName]);
            fputcsv($file, [$bankName]);     
            fputcsv($file, [$accountNum]);   
            fputcsv($file, [$monthLabel]);
            fputcsv($file, []); 

            // Column Headers (Shows PAID amount)
            fputcsv($file, ['Employee Name', 'CNIC', 'Amount (Paid)', 'Employee Account Number']);

            foreach ($salarySheet->items as $item) {
                if ($item->payment_status === 'held' || $item->paid_amount <= 0) continue;

                if ($bankAccountId && $item->employee->business_bank_account_id != $bankAccountId) {
                    continue;
                }

                fputcsv($file, [
                    $item->employee->name,
                    $item->employee->cnic ?? '-',
                    $item->paid_amount, // ✅ Uses Actual Paid Amount
                    $item->employee->bank_account_number ?? 'Cash'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printSheet(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $salarySheet->load(['items.employee', 'business']);
        
        foreach ($salarySheet->items as $item) {
            $calculatedData = $this->salaryCalculator->calculateForMonth($item->employee, $salarySheet->month);
            $item->allowances_breakdown = $calculatedData['allowances_breakdown'];
            $item->deductions_breakdown = $calculatedData['deductions_breakdown'];
        }

        $hasEncashment = $salarySheet->items->sum('leave_encashment_amount') > 0;
        $hasArrears = $salarySheet->items->sum('arrears_adjustment') > 0;
        $hasBonus = $salarySheet->items->sum('bonus') > 0;

        $allowanceHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)
            ->where('type', 'allowance')->orderBy('name')->pluck('name');
        $activeAllowances = [];
        foreach ($allowanceHeaders as $header) {
            if ($salarySheet->items->sum(fn($i) => $i->allowances_breakdown[$header] ?? 0) > 0) 
                $activeAllowances[] = $header;
        }

        $deductionHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)
            ->where('type', 'deduction')->orderBy('name')->pluck('name');
        $activeDeductions = [];
        foreach ($deductionHeaders as $header) {
            if (in_array(strtolower($header), ['income tax', 'tax', 'incometax'])) continue;
            if ($salarySheet->items->sum(fn($i) => $i->deductions_breakdown[$header] ?? 0) > 0)
                $activeDeductions[] = $header;
        }
        
        return view('salary.print_sheet', [
            'salarySheet' => $salarySheet,
            'monthName' => $salarySheet->month->format('F, Y'),
            'activeAllowances' => $activeAllowances,
            'activeDeductions' => $activeDeductions,
            'business' => $salarySheet->business,
            'hasEncashment' => $hasEncashment,
            'hasArrears' => $hasArrears,
            'hasBonus' => $hasBonus
        ]);
    }

    public function payslip(SalarySheetItem $salarySheetItem)
    {
        if ($salarySheetItem->salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $payslip = $this->preparePayslipData($salarySheetItem);
        return view('salary.payslip', [
            'payslip' => $payslip,
            'business' => $salarySheetItem->salarySheet->business
        ]);
    }

    public function printAllPayslips(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $business = Business::find(Auth::user()->business_id);
        $payslips = collect();
        foreach ($salarySheet->items as $item) {
            $payslips->push($this->preparePayslipData($item));
        }
        return view('salary.print_all', compact('payslips', 'business'));
    }

    public function sendAllPayslips(SalarySheet $salarySheet, MailConfigurationService $mailConfigService)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $business = Business::find(Auth::user()->business_id);
        try {
            $mailConfigService->setBusinessMailConfig($business->id);
        } catch (\Exception $e) { return redirect()->back()->with('error', 'Email config error.'); }

        $sentCount = 0;
        foreach ($salarySheet->items as $item) {
            if (filter_var($item->employee->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $payslipData = $this->preparePayslipData($item);
                    $pdf = PDF::loadView('salary.payslip', ['payslip' => $payslipData, 'business' => $business, 'isPdf' => true]);
                    Mail::to($item->employee->email)->send(new PayslipEmail($payslipData, $business, $pdf));
                    $sentCount++;
                } catch (\Exception $e) { Log::error("Email failed: " . $e->getMessage()); }
            }
        }
        return redirect()->back()->with('success', "$sentCount payslips queued.");
    }
    
    public function calculateTaxApi(Request $request)
    {
        $validated = $request->validate(['gross_salary' => 'required|numeric|min:0']);
        $monthlyTax = $this->taxCalculator->calculateMonthlyTaxFromGross((float) $validated['gross_salary'], Carbon::now());
        return response()->json(['monthly_tax' => $monthlyTax]);
    }

    public function destroy(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }

        $newerSheetExists = SalarySheet::where('business_id', Auth::user()->business_id)
            ->where('month', '>', $salarySheet->month)
            ->exists();

        if ($newerSheetExists) {
            return back()->with('error', 'Cannot delete this Salary Sheet because a newer sheet exists. Delete newer sheets first.');
        }

        $salarySheet->delete();
        return redirect()->route('salaries.index')->with('success', 'Salary Sheet deleted successfully.');
    }

    // =============================================================================================
    // 2. TAX CERTIFICATES & EMPLOYEE PORTAL
    // =============================================================================================

    public function myHistory()
    {
        $employee = Auth::user()->employee;
        if (!$employee) return back()->with('error', 'No employee record linked.');
        $payslips = SalarySheetItem::where('employee_id', $employee->id)
            ->whereHas('salarySheet', fn($q) => $q->where('status', 'finalized'))
            ->with('salarySheet')
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        return view('salary.my_history', compact('payslips'));
    }

    public function myTaxCertificate()
    {
        $employee = Auth::user()->employee;
        if (!$employee) abort(403);

        $fys = $this->getFinancialYears();
        return view('salary.my_tax_certificate', compact('fys'));
    }

    public function viewTaxCertificate(Request $request)
    {
        $request->validate(['fy' => 'required|string']);
        $employee = Auth::user()->employee ?? Employee::findOrFail($request->employee_id);
        
        // Permission Check
        if(Auth::user()->employee && Auth::user()->employee->id != $employee->id) abort(403);
        if(!Auth::user()->employee && Auth::user()->business_id != $employee->business_id) abort(403);

        $data = $this->getTaxData($employee, $request->fy);
        return view('salary.view_tax_certificate', $data);
    }

    public function downloadTaxCertificate(Request $request)
    {
        $request->validate(['fy' => 'required|string']);
        $employee = Auth::user()->employee ?? Employee::findOrFail($request->employee_id);

        $data = $this->getTaxData($employee, $request->fy);
        $pdf = PDF::loadView('salary.print_tax_certificate', $data);
        
        return $pdf->download('Tax_Certificate_' . $request->fy . '.pdf');
    }

    public function emailTaxCertificates(Request $request, MailConfigurationService $mailConfigService)
    {
        $request->validate(['fy' => 'required|string']);
        $businessId = Auth::user()->business_id;
        
        try {
            $mailConfigService->setBusinessMailConfig($businessId);
        } catch (\Exception $e) { return back()->with('error', 'Email settings not configured.'); }

        $query = Employee::where('business_id', $businessId)->where('status', 'active');
        if ($request->employee_id) $query->where('id', $request->employee_id);

        $employees = $query->get();
        $sentCount = 0;

        foreach ($employees as $employee) {
            if (!filter_var($employee->email, FILTER_VALIDATE_EMAIL)) continue;

            $data = $this->getTaxData($employee, $request->fy);
            
            if ($data['totalTax'] > 0) {
                try {
                    $pdf = PDF::loadView('salary.print_tax_certificate', $data);
                    Mail::to($employee->email)->send(new TaxCertificateEmail($employee, $data['business'], $request->fy, $pdf));
                    $sentCount++;
                } catch (\Exception $e) { Log::error("Tax Email Failed: " . $e->getMessage()); }
            }
        }

        return back()->with('success', "$sentCount Tax Certificates queued.");
    }

    // =============================================================================================
    // 3. HELPERS
    // =============================================================================================

    private function preparePayslipData(SalarySheetItem $salarySheetItem)
    {
        $payslip = $salarySheetItem;
        $employee = $payslip->employee;

        // ✅ 1. SMART LOOKUP: Designation (Direct Property + Relation Fallback)
        $designationName = '-';
        if (!empty($employee->designation) && is_string($employee->designation)) {
            $designationName = $employee->designation;
        } elseif ($employee->designationRelation) {
            $designationName = $employee->designationRelation->title ?? '-';
        } elseif ($employee->designation_id) {
            $d = Designation::find($employee->designation_id);
            if ($d) $designationName = $d->title;
        }
        $payslip->designation_name = $designationName; 

        // ✅ 2. SMART LOOKUP: Department (Direct Property + Relation Fallback)
        $departmentName = '-';
        if (!empty($employee->department) && is_string($employee->department)) {
            $departmentName = $employee->department;
        } elseif ($employee->departmentRelation) {
            $departmentName = $employee->departmentRelation->name ?? '-';
        } elseif ($employee->department_id) {
            $d = Department::find($employee->department_id);
            if ($d) $departmentName = $d->name;
        }
        $payslip->department_name = $departmentName; 

        // Financials
        $calculatedData = $this->salaryCalculator->calculateForMonth($payslip->employee, $payslip->salarySheet->month);
        $payslip->employee->basic_salary = $calculatedData['basic_salary'];
        
        $earnings = [];
        $earnings['Basic Salary'] = $payslip->employee->basic_salary;
        foreach($calculatedData['allowances_breakdown'] as $name => $amount) {
            if($amount > 0) $earnings[$name] = $amount;
        }
        if ($payslip->leave_encashment_amount > 0) $earnings['Leave Encashment'] = $payslip->leave_encashment_amount;
        if ($payslip->bonus > 0) $earnings['Bonus'] = $payslip->bonus;
        $payslip->prepared_earnings = $earnings;

        $rawDeductions = $calculatedData['deductions_breakdown'];
        $deductions = [];
        foreach($rawDeductions as $name => $amount) {
            if(in_array(strtolower($name), ['income tax', 'tax', 'incometax'])) continue;
            if($amount > 0) $deductions[$name] = $amount;
        }
        if($payslip->income_tax > 0) $deductions['Income Tax'] = $payslip->income_tax;
        $payslip->prepared_deductions = $deductions;
        $payslip->total_deductions_display = array_sum($deductions);

        $funds = Fund::where('business_id', $salarySheetItem->salarySheet->business_id)->get();
        $fundBalances = [];
        $cutoffDate = $salarySheetItem->salarySheet->month->copy()->endOfMonth();

        foreach($funds as $fund) {
            $balance = FundContribution::where('employee_id', $salarySheetItem->employee_id)
                ->where('fund_id', $fund->id)
                ->where('transaction_date', '<=', $cutoffDate)
                ->get()
                ->sum(function($tx) {
                    return in_array($tx->type, ['withdrawal', 'loan_against_fund']) ? -1 * $tx->amount : $tx->amount;
                });

            if($balance > 0) $fundBalances[$fund->name] = $balance;
        }
        $payslip->fund_balances = $fundBalances;

        $payslip->month = $salarySheetItem->salarySheet->month->format('F');
        $payslip->year = $salarySheetItem->salarySheet->month->year;

        if (class_exists(NumberHelper::class)) {
            $payslip->net_salary_in_words = NumberHelper::numberToWords(round($payslip->paid_amount > 0 ? $payslip->paid_amount : $payslip->payable_amount));
        } else {
            $payslip->net_salary_in_words = "Number Helper not found.";
        }
        return $payslip;
    }

    private function getFinancialYears()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $fyStartYear = ($currentMonth >= 7) ? $currentYear : $currentYear - 1;
        
        $fys = [];
        for ($i = 0; $i < 3; $i++) {
            $y = $fyStartYear - $i;
            $nextY = $y + 1;
            $fys["$y-$nextY"] = "Jul-$y to Jun-$nextY";
        }
        return $fys;
    }

    /**
     * ✅ NEW: Bulk Print Tax Certificates
     */
    public function printAllTaxCertificates(Request $request)
    {
        $request->validate([
            'fy' => 'required|string', // Expected format "2025-2026"
        ]);

        $businessId = Auth::user()->business_id;

        // Filter logic (Same as Report)
        $query = Employee::where('business_id', $businessId)->where('status', 'active');

        if ($request->employee_id) {
            $query->where('id', $request->employee_id);
        }

        $employees = $query->get();
        $certificates = collect();

        foreach ($employees as $employee) {
            $taxData = $this->getTaxData($employee, $request->fy);
            
            // Only include if they have tax deducted (Optional, but cleaner)
            if ($taxData['totalTax'] > 0) {
                $certificates->push($taxData);
            }
        }

        if ($certificates->isEmpty()) {
            return back()->with('error', 'No tax deductions found for the selected criteria.');
        }

        return view('salary.print_all_tax_certificates', compact('certificates'));
    }

    private function getTaxData(Employee $employee, $fyString)
    {
        list($startYear, $endYear) = explode('-', $fyString);
        $startDate = Carbon::create($startYear, 7, 1);
        $endDate = Carbon::create($endYear, 6, 30);

        $items = SalarySheetItem::where('employee_id', $employee->id)
            ->whereHas('salarySheet', function($q) use ($startDate, $endDate) {
                $q->where('status', 'finalized')
                  ->whereBetween('month', [$startDate, $endDate]);
            })
            ->with('salarySheet')
            ->get();

        $totalIncome = $items->sum('gross_salary');
        $totalTax = $items->sum('income_tax');
        $business = Business::find($employee->business_id);

        // Smart Lookup for Certificate
        $designation = $employee->designation ?? ($employee->designationRelation->title ?? '-');
        $department = $employee->department ?? ($employee->departmentRelation->name ?? '-');

        return [
            'employee' => $employee,
            'business' => $business,
            'fy' => $fyString,
            'periodText' => "Jul-$startYear to Jun-$endYear",
            'items' => $items,
            'totalIncome' => $totalIncome,
            'totalTax' => $totalTax,
            'designation' => $designation,
            'department' => $department,
            'date' => now()->format('d M, Y')
        ];
    }
}