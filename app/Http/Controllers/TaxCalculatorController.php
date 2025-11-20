<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Services\TaxCalculatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaxCalculatorController extends Controller
{
    public function show(Request $request, Employee $employee = null)
    {
        $businessId = Auth::user()->business_id;
        $isPopup = $request->boolean('popup');
        
        $allAllowances = SalaryComponent::where('business_id', $businessId)
            ->where('type', 'allowance')->orderBy('name')->get();

        $basic = 0;
        $employeeName = '';
        $employeeAllowances = collect();
        $joiningDate = now(); 
        $isBeforeTaxYear = false;

        if ($employee) {
            // Edit Mode
            $employee->load(['salaryComponents', 'business', 'designation']);
            $basic = (float) $employee->basic_salary;
            $employeeName = $employee->name;
            $employeeAllowances = $employee->salaryComponents->pluck('pivot.amount', 'id');
            $joiningDate = $employee->joining_date ? Carbon::parse($employee->joining_date) : now();
            $isBeforeTaxYear = $this->checkIfBeforeTaxYear($joiningDate);
        } else {
            // Create Mode: Read from URL
            $basic = (float)$request->query('basic', 0);
            $employeeName = $request->query('name', '');
            
            // ✅ Read joining date from URL
            if ($request->has('joining_date')) {
                $joiningDate = Carbon::parse($request->query('joining_date'));
            }

            $urlComponents = $request->query('components', []);
            if (is_array($urlComponents)) {
                $employeeAllowances = collect($urlComponents)->map(fn($val) => (float)$val);
            }
        }

        return view('tools.tax-calculator', compact(
            'employee', 'employeeName', 'basic', 'allAllowances', 
            'employeeAllowances', 'joiningDate', 'isBeforeTaxYear', 'isPopup'
        ));
    }

    public function calculate(Request $request, TaxCalculatorService $taxService)
    {
        $validated = $request->validate([
            'employee_id'     => 'nullable|integer|exists:employees,id',
            'employee_name'   => 'nullable|string',
            'monthly_salary'  => 'required|string',
            'tax_year'        => 'required|string',
            'joining_date'    => 'nullable|date|required_if:joined_before_tax_year,0',
            'joined_before_tax_year' => 'nullable|boolean',
            'components'      => 'nullable|array',
            'is_popup'        => 'nullable|boolean',
        ], ['joining_date.required_if' => 'The Joining Date is required.']);

        $basic = $this->unformatNumber($validated['monthly_salary']);
        $selectedTaxYear = (int) $validated['tax_year'];
        $employeeName = $validated['employee_name'] ?? 'Manual Entry';
        
        // Date Logic
        $joinedBefore = $request->boolean('joined_before_tax_year');
        $fyStart = Carbon::create($selectedTaxYear, 7, 1);
        
        if ($joinedBefore) {
            $joiningDate = $fyStart->copy();
        } else {
            $joiningDate = Carbon::parse($validated['joining_date']);
            if ($joiningDate->lt($fyStart)) $joiningDate = $fyStart->copy();
        }

        // Construct Employee
        if (!empty($validated['employee_id'])) {
            $employee = Employee::with(['business'])->find($validated['employee_id']);
            $employee->joining_date = $joiningDate;
            $employee->basic_salary = $basic;
        } else {
            $employee = new Employee();
            $employee->name = $employeeName;
            $employee->business_id = Auth::user()->business_id;
            $employee->joining_date = $joiningDate;
            $employee->basic_salary = $basic;
            $employee->setRelation('business', Auth::user()->business);
        }

        // Components Logic
        $componentInputs = $validated['components'] ?? [];
        $componentCollection = collect();
        $allAllowances = SalaryComponent::where('business_id', Auth::user()->business_id)
                            ->where('type', 'allowance')->orderBy('name')->get();
        $totalAllowances = 0;

        foreach ($allAllowances as $comp) {
            $amount = 0;
            if (isset($componentInputs[$comp->id])) {
                $amount = $this->unformatNumber($componentInputs[$comp->id]);
            }
            if ($amount > 0) {
                $totalAllowances += $amount;
                $clonedComp = clone $comp;
                $clonedComp->pivot = (object)['amount' => $amount];
                $componentCollection->push($clonedComp);
            }
        }
        $employee->setRelation('salaryComponents', $componentCollection);
        
        // Calculate
        $monthlyGross = $basic + $totalAllowances;
        $result = $taxService->calculateAnnualTaxFromGross($monthlyGross, $employee, $validated['tax_year']);

        return view('tools.tax-calculator', [
            'taxData' => [
                'tax_year'         => $result['tax_year'],
                'annual_income'    => $result['annual_gross'],
                'taxable_income'   => $result['annual_taxable'],
                'tax_payable'      => $result['annual_tax'],
                'monthly_tax'      => $result['avg_monthly_tax'],
                'monthly_breakdown'=> $result['monthly_breakdown'],
                'effective_rate'   => ($result['annual_gross'] > 0)
                    ? round(($result['annual_tax'] / $result['annual_gross']) * 100, 2)
                    : 0,
                'date_calculated'  => now()->format('d-M-Y'),
            ],
            'employee' => empty($validated['employee_id']) ? null : $employee,
            'employeeName' => $employeeName,
            'basic' => $basic,
            'allAllowances' => $allAllowances,
            'employeeAllowances' => collect($componentInputs)->map(fn($val) => $this->unformatNumber($val)),
            'joiningDate' => $joiningDate,
            'isBeforeTaxYear' => $joinedBefore,
            'isPopup' => $request->boolean('is_popup')
        ]);
    }
    
    public function apiCalculate(Request $request) { return response()->json(['message' => 'API disabled']); }

    private function unformatNumber(?string $val): float
    {
        if ($val === null) return 0.0;
        $v = trim(str_replace(',', '', $val));
        $n = (float) ($v === '' ? 0 : $v);
        return is_nan($n) ? 0.0 : $n;
    }
    
    private function checkIfBeforeTaxYear($date)
    {
        $currentFyStart = $date->month >= 7 ? Carbon::create($date->year, 7, 1) : Carbon::create($date->year - 1, 7, 1);
        return $date->lt($currentFyStart);
    }
}