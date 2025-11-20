<?php

namespace App\Http\Controllers;

use App\Helpers\NumberHelper;
use App\Models\Business;
use App\Models\Employee;
use App\Models\SalarySheet;
use App\Models\SalarySheetItem;
use App\Models\SalaryComponent;
use App\Services\SalaryCalculationService;
use App\Services\TaxCalculatorService;
use App\Services\MailConfigurationService;
use App\Mail\PayslipEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SalaryController extends Controller
{
    protected $taxCalculator;
    protected $salaryCalculator;

    public function __construct(TaxCalculatorService $taxCalculator, SalaryCalculationService $salaryCalculator)
    {
        $this->taxCalculator = $taxCalculator;
        $this->salaryCalculator = $salaryCalculator;
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
        
        $employees = Employee::where('business_id', $businessId)
            ->where('status', 'active')
            ->with(['salaryComponents', 'incentives'])
            ->get();

        if ($employees->isEmpty()) {
            return redirect()->route('salaries.create')->with('error', 'Cannot generate sheet: No active employees found.');
        }
        
        $problematicEmployees = [];
        foreach ($employees as $employee) {
            if (is_null($employee->basic_salary)) {
                $problematicEmployees[] = $employee->name . ' (ID: ' . $employee->id . ')';
            }
        }

        if (!empty($problematicEmployees)) {
            $errorMessage = 'Cannot generate sheet. The following active employees are missing a Basic Salary: ' . implode(', ', $problematicEmployees) . '. Please update their profiles.';
            return redirect()->route('salaries.create')->with('error', $errorMessage);
        }

        DB::beginTransaction();
        try {
            $sheet = SalarySheet::create([
                'business_id' => $businessId,
                'month' => $month->toDateString(),
                'status' => 'generated'
            ]);

            foreach ($employees as $employee) {
                $salaryData = $this->salaryCalculator->calculateForMonth($employee, $month);
                $incomeTax = $this->taxCalculator->calculate($employee, $month);
                $netSalary = $salaryData['gross_salary'] - $salaryData['total_deductions_components'] - $incomeTax;

                SalarySheetItem::create([
                    'salary_sheet_id' => $sheet->id,
                    'employee_id' => $employee->id,
                    'gross_salary' => $salaryData['gross_salary'],
                    'bonus' => $salaryData['bonus'],
                    'deductions' => $salaryData['total_deductions_components'],
                    'income_tax' => $incomeTax,
                    'net_salary' => $netSalary,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return redirect()->route('salaries.show', $sheet->id)->with('success', 'Salary Sheet generated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Salary generation failed: " . $e->getMessage());
            return redirect()->route('salaries.create')->with('error', 'An error occurred while generating the salary sheet. Error: ' . $e->getMessage());
        }
    }

    public function show(SalarySheet $salarySheet)
    {
        if ($salarySheet->business_id !== Auth::user()->business_id) { abort(403); }
        
        $salarySheet->load(['items.employee', 'business']);
        
        $allowanceHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'allowance')->pluck('name');
        $deductionHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'deduction')->pluck('name');
        
        foreach ($salarySheet->items as $item) {
            $item->employee->load('salaryComponents');
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
        $salarySheet->load(['items.employee', 'business']);
        $allowanceHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'allowance')->pluck('name');
        $deductionHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'deduction')->pluck('name');
        foreach ($salarySheet->items as $item) {
            $item->employee->load('salaryComponents');
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
    
    /**
     * ✅ NEW: API method to calculate monthly tax for a given gross salary.
     */
    public function calculateTaxApi(Request $request)
    {
        $validated = $request->validate([
            'gross_salary' => 'required|numeric|min:0',
        ]);

        $monthlyTax = $this->taxCalculator->calculateMonthlyTaxFromGross(
            (float) $validated['gross_salary'],
            Carbon::now() // Use current date to find the active tax slab
        );

        return response()->json([
            'monthly_tax' => $monthlyTax,
        ]);
    }

    private function preparePayslipData(SalarySheetItem $salarySheetItem)
    {
        $payslip = $salarySheetItem;
        $payslip->load('employee.salaryComponents');
        $allowances = [];
        $deductions = [];

        if ($payslip->bonus > 0) {
            $allowances['Bonus'] = $payslip->bonus;
        }

        foreach ($payslip->employee->salaryComponents as $component) {
            if ($component->type === 'allowance') {
                $allowances[$component->name] = $component->pivot->amount;
            } else {
                $deductions[$component->name] = $component->pivot->amount;
            }
        }
        $payslip->allowances_breakdown = $allowances;
        $payslip->deductions_breakdown = $deductions;
        $payslip->total_deductions = array_sum($deductions) + $payslip->income_tax;
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