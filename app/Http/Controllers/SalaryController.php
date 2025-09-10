<?php

namespace App\Http\Controllers;

use App\Helpers\NumberHelper;
use App\Models\Business;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\SalarySheet;
use App\Models\SalarySheetItem;
use App\Services\TaxCalculatorService;
use App\Services\MailConfigurationService;
use App\Mail\PayslipEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SalaryController extends Controller
{
    protected $taxCalculator;

    public function __construct(TaxCalculatorService $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

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

    public function generate(Request $request)
    {
        $request->validate(['month' => 'required|date_format:Y-m']);
        $businessId = Auth::user()->business_id;
        $month = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();

        $existingSheet = SalarySheet::where('business_id', $businessId)
                                    ->whereYear('month', $month->year)
                                    ->whereMonth('month', $month->month)
                                    ->first();
        if ($existingSheet) {
            return redirect()->route('salaries.index')->with('error', 'A salary sheet for ' . $month->format('F, Y') . ' already exists.');
        }
        
        $employees = Employee::where('business_id', $businessId)->where('status', 'active')->with('salaryComponents')->get();
        if ($employees->isEmpty()) {
            return redirect()->route('salaries.create')->with('error', 'Cannot generate sheet: No active employees found.');
        }

        try {
            $sheet = DB::transaction(function () use ($businessId, $month, $employees) {
                $sheet = SalarySheet::create([
                    'business_id' => $businessId,
                    'month' => $month->toDateString(),
                    'status' => 'generated'
                ]);

                foreach ($employees as $employee) {
                    $totalAllowances = $employee->salaryComponents->where('type', 'allowance')->sum('pivot.amount');
                    $grossSalary = (float) $employee->basic_salary + $totalAllowances;
                    $incomeTax = $this->taxCalculator->calculate((object)['gross_salary' => $grossSalary], $month);
                    $totalDeductionsFromComponents = $employee->salaryComponents->where('type', 'deduction')->sum('pivot.amount');
                    $netSalary = $grossSalary - $incomeTax - $totalDeductionsFromComponents;

                    SalarySheetItem::create([
                        'salary_sheet_id' => $sheet->id,
                        'employee_id' => $employee->id,
                        'gross_salary' => $grossSalary,
                        'deductions' => $totalDeductionsFromComponents,
                        'income_tax' => $incomeTax,
                        'net_salary' => $netSalary,
                        'status' => 'pending',
                    ]);
                }
                return $sheet;
            });
            return redirect()->route('salaries.show', $sheet->id)->with('success', 'Salary Sheet generated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('salaries.create')->with('error', 'An error occurred while generating the salary sheet. Please try again. Error: ' . $e->getMessage());
        }
    }
    
    public function show(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $salarySheet->load('items.employee.salaryComponents', 'business');
        $allowanceHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'allowance')->pluck('name');
        $deductionHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'deduction')->pluck('name');
        foreach ($salarySheet->items as $item) {
            $item->allowances_breakdown = $item->employee->salaryComponents->where('type', 'allowance')->pluck('pivot.amount', 'name');
            $item->deductions_breakdown = $item->employee->salaryComponents->where('type', 'deduction')->pluck('pivot.amount', 'name');
        }
        $monthName = $salarySheet->month->format('F, Y');
        $business = $salarySheet->business;
        return view('salary.show', compact('salarySheet', 'monthName', 'allowanceHeaders', 'deductionHeaders', 'business'));
    }
    
    public function printSheet(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $salarySheet->load('items.employee.salaryComponents', 'business');
        $allowanceHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'allowance')->pluck('name');
        $deductionHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'deduction')->pluck('name');
        foreach ($salarySheet->items as $item) {
            $item->allowances_breakdown = $item->employee->salaryComponents->where('type', 'allowance')->pluck('pivot.amount', 'name');
            $item->deductions_breakdown = $item->employee->salaryComponents->where('type', 'deduction')->pluck('pivot.amount', 'name');
        }
        $monthName = $salarySheet->month->format('F, Y');
        $business = $salarySheet->business;
        return view('salary.print_sheet', compact('salarySheet', 'monthName', 'allowanceHeaders', 'deductionHeaders', 'business'));
    }

    public function payslip(SalarySheetItem $salarySheetItem)
    {
        if ($salarySheetItem->salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $payslip = $this->preparePayslipData($salarySheetItem);
        $business = Business::find(Auth::user()->business_id);
        return view('salary.payslip', compact('payslip', 'business'));
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
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Email configuration is not set up correctly. Please check your settings.');
        }

        $sentCount = 0;
        try {
            foreach ($salarySheet->items as $item) {
                if (filter_var($item->employee->email, FILTER_VALIDATE_EMAIL)) {
                    $payslipData = $this->preparePayslipData($item);
                    // ** THIS IS THE FIX: Pass a flag to the view when generating the PDF **
                    $pdf = PDF::loadView('salary.payslip', [
                        'payslip' => $payslipData, 
                        'business' => $business, 
                        'isPdf' => true
                    ]);
                    Mail::to($item->employee->email)->send(new PayslipEmail($payslipData, $business, $pdf));
                    $sentCount++;
                }
            }
        } catch (TransportExceptionInterface $e) {
            return redirect()->back()->with('error', 'Failed to send emails. The mail server responded with: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', "$sentCount payslips have been queued for sending.");
    }

    private function preparePayslipData(SalarySheetItem $salarySheetItem)
    {
        $payslip = $salarySheetItem;
        $payslip->load('employee.salaryComponents');
        $allowances = [];
        $deductions = [];
        foreach ($payslip->employee->salaryComponents as $component) {
            if ($component->type === 'allowance') {
                $allowances[$component->name] = $component->pivot->amount;
            } else {
                $deductions[$component->name] = $component->pivot->amount;
            }
        }
        $payslip->allowances_breakdown = $allowances;
        $payslip->deductions_breakdown = $deductions;
        $payslip->total_deductions = array_sum($deductions);
        $payslip->month = $salarySheetItem->salarySheet->month->format('F');
        $payslip->year = $salarySheetItem->salarySheet->month->year;

        if (class_exists(NumberHelper::class)) {
            $payslip->net_salary_in_words = NumberHelper::numberToWords(round($payslip->net_salary));
        } else {
            $payslip->net_salary_in_words = "Number Helper not found.";
        }
        return $payslip;
    }

    public function destroy(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        $salarySheet->delete();
        return redirect()->route('salaries.index')->with('success', 'Salary Sheet deleted successfully.');
    }
}