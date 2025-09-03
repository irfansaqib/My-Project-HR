<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Employee;
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
        $businessId = Auth::user()->business_id;
        $processedMonths = SalarySheet::where('business_id', $businessId)
            ->select(
                DB::raw('YEAR(month) as year'),
                DB::raw('MONTH(month) as month')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')->orderBy('month', 'desc')
            ->get();
            
        foreach($processedMonths as $pm) {
            $sheet = SalarySheet::where('business_id', $businessId)
                ->whereYear('month', $pm->year)
                ->whereMonth('month', $pm->month)
                ->first();
            
            if ($sheet) {
                $pm->sheet_id = $sheet->id;
                $pm->payslip_count = $sheet->items->count();
            } else {
                $pm->sheet_id = 0;
                $pm->payslip_count = 0;
            }
        }

        return view('salary.index', compact('processedMonths'));
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

        $sheet = SalarySheet::updateOrCreate(
            ['business_id' => $businessId, 'month' => $month->toDateString()],
            ['status' => 'generated']
        );

        $sheet->items()->delete();
        
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
        $salarySheet->load('items.employee');
        $monthName = Carbon::parse($salarySheet->month)->format('F');
        $year = Carbon::parse($salarySheet->month)->year;
        $payslips = $salarySheet->items;
        return view('salary.show', compact('salarySheet', 'monthName', 'year', 'payslips'));
    }

    public function payslip(SalarySheetItem $salarySheetItem)
    {
        $this->authorize('view', $salarySheetItem->salarySheet);
        $payslip = $salarySheetItem;
        $business = Business::find(Auth::user()->business_id);
        
        $payslip->allowances_breakdown = [];
        $payslip->deductions_breakdown = [];
        
        return view('salary.payslip', compact('payslip', 'business'));
    }

    public function destroy(SalarySheet $salarySheet)
    {
        $this->authorize('delete', $salarySheet);
        $salarySheet->delete();
        return redirect()->route('salaries.index')->with('success', 'Salary Sheet deleted successfully.');
    }
}