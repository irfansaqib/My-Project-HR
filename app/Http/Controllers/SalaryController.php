<?php

namespace App\Http\Controllers;

use App\Helpers\NumberHelper;
use App\Models\Business;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\SalarySheet;
use App\Models\SalarySheetItem;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    protected $taxCalculator;

    public function __construct(TaxCalculatorService $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

    public function index()
    {
        // --- THIS METHOD IS NOW CORRECTED ---
        $businessId = Auth::user()->business_id;
        $salarySheets = SalarySheet::where('business_id', $businessId)
            ->withCount('items') // Count the number of items (payslips)
            ->orderBy('month', 'desc')
            ->paginate(10); // Use pagination for better performance

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

        $sheet = SalarySheet::create([
            'business_id' => $businessId,
            'month' => $month->toDateString(),
            'status' => 'generated'
        ]);
        
        $employees = Employee::where('business_id', $businessId)->with('salaryComponents')->get();

        foreach ($employees as $employee) {
            $incomeTax = $this->taxCalculator->calculate($employee, $month);
            $netSalary = (float) $employee->gross_salary - $incomeTax;

            SalarySheetItem::create([
                'salary_sheet_id' => $sheet->id,
                'employee_id' => $employee->id,
                'gross_salary' => $employee->gross_salary,
                'deductions' => 0,
                'income_tax' => $incomeTax,
                'net_salary' => $netSalary,
            ]);
        }

        return redirect()->route('salaries.show', $sheet->id)->with('success', 'Salary Sheet generated successfully.');
    }
    
    public function show(SalarySheet $salarySheet)
    {
        $this->authorize('view', $salarySheet);
        $salarySheet->load('items.employee.salaryComponents', 'business');
        $allowanceHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'allowance')->pluck('name');
        $deductionHeaders = SalaryComponent::where('business_id', auth()->user()->business_id)->where('type', 'deduction')->pluck('name');
        foreach ($salarySheet->items as $item) {
            $item->allowances = $item->employee->salaryComponents->where('type', 'allowance')->pluck('pivot.amount', 'name');
            $item->deductions = $item->employee->salaryComponents->where('type', 'deduction')->pluck('pivot.amount', 'name');
        }
        $monthName = Carbon::parse($salarySheet->month)->format('F, Y');
        $business = $salarySheet->business;
        return view('salary.show', compact('salarySheet', 'monthName', 'allowanceHeaders', 'deductionHeaders', 'business'));
    }

    public function payslip(SalarySheetItem $salarySheetItem)
    {
        $this->authorize('view', $salarySheetItem->salarySheet);
        $payslip = $salarySheetItem;
        $business = Business::find(Auth::user()->business_id);
        $payslip->load('employee.salaryComponents');
        $allowances = [];
        $deductions = [];
        foreach ($payslip->employee->salaryComponents as $component) {
            $component->type === 'allowance' ? $allowances[$component->name] = $component->pivot->amount : $deductions[$component->name] = $component->pivot->amount;
        }
        $payslip->allowances_breakdown = $allowances;
        $payslip->deductions_breakdown = $deductions;
        $payslip->total_deductions = array_sum($deductions);
        $payslip->month = Carbon::parse($payslip->salarySheet->month)->format('F');
        $payslip->year = Carbon::parse($payslip->salarySheet->month)->year;
        if(class_exists(NumberHelper::class)) {
            $payslip->net_salary_in_words = NumberHelper::numberToWords($payslip->net_salary);
        } else {
            $payslip->net_salary_in_words = "Number Helper not found.";
        }
        return view('salary.payslip', compact('payslip', 'business'));
    }

    public function destroy(SalarySheet $salarySheet)
    {
        $this->authorize('delete', $salarySheet);
        $salarySheet->delete();
        return redirect()->route('salaries.index')->with('success', 'Salary Sheet deleted successfully.');
    }
}