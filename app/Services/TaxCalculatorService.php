<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaxRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaxCalculatorService
{
    /**
     * Method A: Payroll Monthly Tax (Linked to Employee & History)
     * Used by: SalaryController (Salary Sheet Generation)
     */
    public function calculate(Employee $employee, Carbon $payrollDate, ?float $grossOverride = null): float
    {
        $fyStart = $this->getFinancialYearStart($payrollDate);
        $fyEnd   = $fyStart->copy()->addYear()->subDay();

        $taxRateRecord = $this->getActiveTaxRateByBusiness($fyStart, $employee->business_id);
        if (!$taxRateRecord) return 0.0;

        // Fetch tax already paid in this FY
        $history = $employee->salarySheetItems()
            ->whereHas('salarySheet', function ($q) use ($fyStart, $payrollDate) {
                $q->where('month', '>=', $fyStart)->where('month', '<', $payrollDate);
            })->get();

        $taxPaid = (float) $history->sum('income_tax');
        $currentGross = $grossOverride ?? (float) $employee->gross_salary;

        $joiningDate = Carbon::parse($employee->joining_date ?? $fyStart)->startOfDay();
        if ($joiningDate->lt($fyStart)) $joiningDate = $fyStart->copy();

        // Calculate Projected Annual Gross
        $proj = $this->calculateProjectedAnnualGross($currentGross, $joiningDate, $fyStart, $fyEnd);
        $projectedAnnualGross = $proj['annual_gross'];
        
        // Calculate Annual Taxable Income (Allowances Exemptions applied here)
        $annualTaxableIncome = $this->getAnnualTaxableIncome($employee, $projectedAnnualGross, $joiningDate, $fyEnd);
        
        // Calculate Total Annual Tax (Includes Surcharge Logic Now)
        $totalAnnualTax = $this->calculateFromAnnualGross($annualTaxableIncome, $taxRateRecord);

        // Deduct what has already been paid
        $remainingTax = max($totalAnnualTax - $taxPaid, 0);
        
        // Count remaining payroll months
        $remainingMonths = 0;
        $current = $payrollDate->copy()->startOfMonth();
        while($current->lte($fyEnd)) {
             $remainingMonths++;
             $current->addMonth();
        }
        
        // Return average tax for remaining months
        return $remainingMonths > 0 ? round($remainingTax / $remainingMonths, 2) : 0;
    }

    /**
     * Method B: Advanced Reconciliation Logic (For Bulk Tool & Tax Services)
     */
    public function calculateReconciledTax(
        float $currentMonthlyTaxable, 
        float $ytdIncome, 
        float $taxAlreadyDeducted, 
        int $monthsRemaining, 
        float $bonus, 
        Carbon $taxDate
    ) {
        $futureIncome = $currentMonthlyTaxable * $monthsRemaining;
        $totalAnnualTaxable = $ytdIncome + $futureIncome + $bonus;
        $totalAnnualTax = $this->calculateTaxFromAnnualIncome($totalAnnualTaxable, $taxDate);
        $remainingTax = max(0, $totalAnnualTax - $taxAlreadyDeducted);
        $newMonthlyTax = ($monthsRemaining > 0) ? ($remainingTax / $monthsRemaining) : 0;

        return [
            'annual_taxable' => $totalAnnualTaxable,
            'total_annual_tax' => $totalAnnualTax,
            'tax_paid_so_far' => $taxAlreadyDeducted,
            'remaining_tax' => $remainingTax,
            'new_monthly_tax' => $newMonthlyTax
        ];
    }

    /**
     * Method C: Direct Annual Calculation
     */
    public function calculateTaxFromAnnualIncome(float $annualTaxableIncome, Carbon $date): float
    {
        $fyStart = $this->getFinancialYearStart($date);
        $businessId = Auth::check() ? Auth::user()->business_id : null;
        
        $taxRateRecord = $this->getActiveTaxRateByBusiness($fyStart, $businessId);
        
        return $this->calculateFromAnnualGross($annualTaxableIncome, $taxRateRecord);
    }

    /**
     * Method D: Monthly Input Calculation (Safety Method)
     */
    public function calculateMonthlyTaxFromGross(float $monthlyGross, Carbon $date): float
    {
        $annualIncome = $monthlyGross * 12;
        $annualTax = $this->calculateTaxFromAnnualIncome($annualIncome, $date);
        return $annualTax / 12;
    }

    /**
     * Method E: Calculator View Helper
     */
    public function calculateAnnualTaxFromGross(float $monthlyGross, Employee $employee, string|int $taxYear): array
    {
        $year = is_numeric($taxYear) ? (int) $taxYear : (int) explode('-', $taxYear)[0];
        $fyStart = Carbon::create($year, 7, 1)->startOfDay();
        $fyEnd   = $fyStart->copy()->addYear()->subDay()->endOfDay();

        $joiningDate = Carbon::parse($employee->joining_date ?? $fyStart)->startOfDay();
        
        if ($joiningDate->lt($fyStart)) $joiningDate = $fyStart->copy();

        if ($joiningDate->gt($fyEnd)) {
            return $this->emptyResult($employee, $year);
        }

        $proj = $this->calculateProjectedAnnualGross($monthlyGross, $joiningDate, $fyStart, $fyEnd);
        $annualGross = $proj['annual_gross'];
        $firstMonthIncome = $proj['first_month_income'];
        $remainingMonthsCount = $proj['remaining_months_count'];

        $annualTaxable = $this->getAnnualTaxableIncome($employee, $annualGross, $joiningDate, $fyEnd);
        $annualTax = $this->calculateFromAnnualGross($annualTaxable, $this->getActiveTaxRateByBusiness($fyStart, $employee->business_id));

        // Breakdown Logic
        $months = [];
        $current = $joiningDate->copy();
        $firstMonthTax = 0;
        $otherMonthsTax = 0;

        if ($annualGross > 0) {
            $firstMonthTax = $annualTax * ($firstMonthIncome / $annualGross);
            if ($remainingMonthsCount > 0) {
                $otherMonthsTax = ($annualTax - $firstMonthTax) / $remainingMonthsCount;
            } else {
                $firstMonthTax = $annualTax;
            }
        }

        while ($current->lte($fyEnd)) {
            $isFirstMonth = $current->month === $joiningDate->month && $current->year === $joiningDate->year;
            $months[] = [
                'month' => $current->format('M-Y'),
                'tax' => $isFirstMonth ? round($firstMonthTax, 2) : round($otherMonthsTax, 2)
            ];
            $current->addMonth()->startOfMonth(); 
        }

        $avgMonthlyTax = $otherMonthsTax > 0 ? $otherMonthsTax : ($months[0]['tax'] ?? 0);

        return [
            'employee_name'      => $employee->name,
            'designation'        => $this->safeDesignation($employee),
            'annual_gross'       => round($annualGross, 0),
            'annual_taxable'     => round($annualTaxable, 0),
            'annual_tax'         => round($annualTax, 0),
            'avg_monthly_tax'    => round($avgMonthlyTax, 0),
            'monthly_breakdown'  => $months,
            'tax_year'           => $this->formatTaxYearLabel($year),
        ];
    }

    // --- INTERNAL CORE HELPERS ---

    /**
     * ✅ CORE SLAB LOGIC + SURCHARGE
     */
    private function calculateFromAnnualGross(float $annualTaxableIncome, ?TaxRate $taxRateRecord): float
    {
        if ($annualTaxableIncome <= 0 || !$taxRateRecord) return 0.0;

        $slabs = $taxRateRecord->slabs ?? [];
        usort($slabs, fn ($a, $b) => ($a['income_from'] ?? 0) <=> ($b['income_from'] ?? 0));

        $tax = 0.0;
        // 1. Calculate base tax
        foreach ($slabs as $slab) {
            $from = (float) ($slab['income_from'] ?? 0);
            $to   = (float) ($slab['income_to'] ?? 999999999999);

            // We check if taxable income falls within the current slab range
            if ($annualTaxableIncome >= $from) {
                $fixed = (float) ($slab['fixed_tax_amount'] ?? 0);
                $rate  = (float) ($slab['tax_rate_percentage'] ?? 0);
                
                // Calculate tax on the amount exceeding the start of the slab
                $excessAmount = $annualTaxableIncome - $from;
                $tax = $fixed + ($excessAmount * ($rate / 100));
            }
        }
        
        // 2. Apply Surcharge
        $surchargeThreshold = (float) ($taxRateRecord->surcharge_threshold ?? 0);
        $surchargeRate = (float) ($taxRateRecord->surcharge_rate_percentage ?? 0); // Corrected property name
        
        if ($surchargeThreshold > 0 && $surchargeRate > 0 && $annualTaxableIncome > $surchargeThreshold) {
            $surchargeAmount = $tax * ($surchargeRate / 100);
            $tax += $surchargeAmount;
        }

        return round($tax, 2);
    }

    private function calculateProjectedAnnualGross(float $monthlyGross, Carbon $joiningDate, Carbon $fyStart, Carbon $fyEnd): array
    {
        $daysInFirstMonth = $joiningDate->daysInMonth;
        $workedDays = $daysInFirstMonth - $joiningDate->day + 1;
        
        $firstMonthIncome = $joiningDate->day == 1 
            ? $monthlyGross 
            : ($workedDays / $daysInFirstMonth) * $monthlyGross;
        
        $firstMonthEnd = $joiningDate->copy()->endOfMonth();
        $remainingMonthsCount = 0;
        $current = $firstMonthEnd->copy()->addDay();
        while($current->lte($fyEnd)) {
            $remainingMonthsCount++;
            $current->addMonth();
        }
        
        $remainingIncome = $remainingMonthsCount * $monthlyGross;
        
        return [
            'annual_gross' => $firstMonthIncome + $remainingIncome,
            'first_month_income' => $firstMonthIncome,
            'remaining_months_count' => $remainingMonthsCount
        ];
    }

    private function getAnnualTaxableIncome(Employee $employee, float $annualGross, Carbon $joiningDate, Carbon $fyEnd): float
    {
        if ($employee->relationLoaded('salaryComponents')) {
            $exemptAllowances = $employee->salaryComponents->where('type', 'allowance')->where('is_tax_exempt', true);
        } else {
            $exemptAllowances = $employee->salaryComponents()->where('type', 'allowance')->where('is_tax_exempt', true)->get();
        }

        $totalExemption = 0.0;
        $daysInFirstMonth = $joiningDate->daysInMonth;
        $workedDays = $daysInFirstMonth - $joiningDate->day + 1;
        $firstMonthRatio = $joiningDate->day == 1 ? 1.0 : ($workedDays / $daysInFirstMonth);
        $firstMonthEnd = $joiningDate->copy()->endOfMonth();
        $remainingMonthsCount = 0;
        $current = $firstMonthEnd->copy()->addDay();
        while($current->lte($fyEnd)) {
            $remainingMonthsCount++;
            $current->addMonth();
        }

        foreach ($exemptAllowances as $allowance) {
            if ($allowance->exemption_type === 'percentage_of_basic' && isset($allowance->exemption_value)) {
                $monthlyBasic = (float) $employee->basic_salary;
                $monthlyExemptLimit = $monthlyBasic * ((float) $allowance->exemption_value / 100);
                
                $firstMonthExemption = $monthlyExemptLimit * $firstMonthRatio;
                $remainingExemption = $monthlyExemptLimit * $remainingMonthsCount;
                
                $totalExemption += ($firstMonthExemption + $remainingExemption);
            }
        }

        return max($annualGross - $totalExemption, 0.0);
    }

    private function getFinancialYearStart(Carbon $date): Carbon
    {
        return $date->month >= 7 ? Carbon::create($date->year, 7, 1) : Carbon::create($date->year - 1, 7, 1);
    }

    private function getActiveTaxRateByBusiness(Carbon $fyStart, ?int $businessId): ?TaxRate
    {
        $q = TaxRate::query()
            ->where('effective_from_date', '<=', $fyStart)
            ->where(function ($query) use ($fyStart) {
                $query->whereNull('effective_to_date')->orWhere('effective_to_date', '>=', $fyStart);
            });

        if ($businessId) $q->where('business_id', $businessId);
        else $q->whereNull('business_id');

        return $q->orderByDesc('effective_from_date')->first() 
            ?? TaxRate::whereNull('business_id')->where('effective_from_date', '<=', $fyStart)->orderByDesc('effective_from_date')->first();
    }

    private function safeDesignation(Employee $employee): string
    {
        return $employee->designation->name ?? ($employee->designationRelation->name ?? 'N/A');
    }

    private function formatTaxYearLabel(int $fyStartYear): string
    {
        return sprintf('%d-%d', $fyStartYear, $fyStartYear + 1);
    }
    
    private function emptyResult($employee, $year) {
        return [
            'employee_name' => $employee->name, 'designation' => 'N/A', 'annual_gross' => 0,
            'annual_taxable' => 0, 'annual_tax' => 0, 'avg_monthly_tax' => 0,
            'monthly_breakdown' => [], 'tax_year' => $this->formatTaxYearLabel($year),
        ];
    }
}