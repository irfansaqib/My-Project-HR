<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    protected $taxCalculator;

    public function __construct(TaxCalculatorService $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

    public function index()
    {
        $processedMonths = Payslip::select('year', 'month', DB::raw('count(*) as payslip_count'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('salaries.index', compact('processedMonths'));
    }

    public function create()
    {
        return view('salaries.create');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $date = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        $year = $date->year;
        $month = $date->month;

        $existing = Payslip::where('year', $year)->where('month', $month)->exists();
        if ($existing) {
            return back()->with('error', "Salaries for {$date->format('F, Y')} have already been generated.");
        }

        $employees = Employee::where('status', 'active')->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'No active employees found to generate salaries for.');
        }

        foreach ($employees as $employee) {
            $allowances = $employee->salaryComponents->where('type', 'allowance');
            $deductions = $employee->salaryComponents->where('type', 'deduction');

            $totalAllowances = $allowances->sum('pivot.amount');
            $totalDeductions = $deductions->sum('pivot.amount');
            
            // Pass the employee and the payroll date to the calculator
            $monthlyTax = $this->taxCalculator->calculate($employee, $date);

            $netSalary = $employee->gross_salary - $totalDeductions - $monthlyTax;

            Payslip::create([
                'business_id' => $employee->business_id,
                'employee_id' => $employee->id,
                'year' => $year,
                'month' => $month,
                'basic_salary' => $employee->basic_salary,
                'total_allowances' => $totalAllowances,
                'total_deductions' => $totalDeductions,
                'gross_salary' => $employee->gross_salary,
                'income_tax' => $monthlyTax,
                'net_salary' => $netSalary,
                'status' => 'generated',
                'allowances_breakdown' => $allowances->pluck('pivot.amount', 'name'),
                'deductions_breakdown' => $deductions->pluck('pivot.amount', 'name'),
            ]);
        }

        return redirect()->route('salaries.index')->with('success', "Salaries for {$employees->count()} employees for {$date->format('F, Y')} have been generated successfully.");
    }

    public function show($year, $month)
    {
        $payslips = Payslip::with('employee')
            ->where('year', $year)
            ->where('month', $month)
            ->get();
        
        if($payslips->isEmpty()){
            abort(404);
        }
            
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');

        return view('salaries.show', compact('payslips', 'year', 'monthName'));
    }

    public function destroy($year, $month)
    {
        Payslip::where('year', $year)->where('month', $month)->delete();
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
        return redirect()->route('salaries.index')->with('success', "Salary sheet for {$monthName}, {$year} has been deleted.");
    }

    public function showPayslip(Payslip $payslip)
    {
        $business = Auth::user()->business;
        return view('salaries.payslip', compact('payslip', 'business'));
    }
}