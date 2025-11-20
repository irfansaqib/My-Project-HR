<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaxRate;
use Carbon\Carbon;

class TaxCalculatorService
{
    /**
     * Payroll-oriented monthly tax (for salary sheet usage)
     */
    public function calculate(Employee $employee, Carbon $payrollDate, ?float $grossOverride = null): float
    {
        $fyStart = $this->getFinancialYearStart($payrollDate);
        $fyEnd   = $fyStart->copy()->addYear()->subDay();

        $taxRateRecord = $this->getActiveTaxRateByBusiness($fyStart, $employee->business_id);
        if (!$taxRateRecord) return 0.0;

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
        
        $annualTaxableIncome = $this->getAnnualTaxableIncome($employee, $projectedAnnualGross, $joiningDate, $fyEnd);
        $totalAnnualTax = $this->calculateFromAnnualGross($annualTaxableIncome, $taxRateRecord);

        $remainingTax = max($totalAnnualTax - $taxPaid, 0);
        
        // Count remaining payroll months
        $remainingMonths = 0;
        $current = $payrollDate->copy()->startOfMonth();
        while($current->lte($fyEnd)) {
             $remainingMonths++;
             $current->addMonth();
        }
        
        return $remainingMonths > 0 ? round($remainingTax / $remainingMonths, 2) : 0;
    }

    /**
     * Calculator view helper
     */
    public function calculateAnnualTaxFromGross(float $monthlyGross, Employee $employee, string|int $taxYear): array
    {
        $year = is_numeric($taxYear) ? (int) $taxYear : (int) explode('-', $taxYear)[0];
        $fyStart = Carbon::create($year, 7, 1)->startOfDay();
        $fyEnd   = $fyStart->copy()->addYear()->subDay()->endOfDay(); // 30th June

        $joiningDate = Carbon::parse($employee->joining_date ?? $fyStart)->startOfDay();
        
        if ($joiningDate->lt($fyStart)) $joiningDate = $fyStart->copy();

        if ($joiningDate->gt($fyEnd)) {
            return $this->emptyResult($employee, $year);
        }

        // 1. Calculate Annual Gross (Exact logic: Partial + Full Months)
        $proj = $this->calculateProjectedAnnualGross($monthlyGross, $joiningDate, $fyStart, $fyEnd);
        $annualGross = $proj['annual_gross'];
        $firstMonthIncome = $proj['first_month_income'];
        $remainingMonthsCount = $proj['remaining_months_count'];

        // 2. Calculate Taxable (Apply same time-weighting to exemptions)
        $annualTaxable = $this->getAnnualTaxableIncome($employee, $annualGross, $joiningDate, $fyEnd);

        // 3. Calculate Tax
        $annualTax = $this->calculateFromAnnualGross($annualTaxable, $this->getActiveTaxRateByBusiness($fyStart, $employee->business_id));

        // 4. Monthly Breakdown
        $months = [];
        $current = $joiningDate->copy();
        
        // Apportion tax based on income ratio
        $firstMonthTax = 0;
        $otherMonthsTax = 0;

        if ($annualGross > 0) {
            // Tax for partial month = Total Tax * (Income of partial month / Total Income)
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

        return [
            'employee_name'      => $employee->name,
            'designation'        => $this->safeDesignation($employee),
            'annual_gross'       => round($annualGross, 0), // Rounded for display
            'annual_taxable'     => round($annualTaxable, 0),
            'annual_tax'         => round($annualTax, 0),
            'avg_monthly_tax'    => round($otherMonthsTax > 0 ? $otherMonthsTax : ($months[0]['tax'] ?? 0), 0),
            'monthly_breakdown'  => $months,
            'tax_year'           => $this->formatTaxYearLabel($year),
        ];
    }

    /**
     * ✅ EXPLICIT CALCULATION LOGIC
     */
    private function calculateProjectedAnnualGross(float $monthlyGross, Carbon $joiningDate, Carbon $fyStart, Carbon $fyEnd): array
    {
        // 1. Partial First Month
        $daysInFirstMonth = $joiningDate->daysInMonth;
        $workedDays = $daysInFirstMonth - $joiningDate->day + 1;
        
        // If joined on 1st, full month. Else proportional.
        $firstMonthIncome = $joiningDate->day == 1 
            ? $monthlyGross 
            : ($workedDays / $daysInFirstMonth) * $monthlyGross;
        
        // 2. Remaining Full Months
        $firstMonthEnd = $joiningDate->copy()->endOfMonth();
        $remainingMonthsCount = 0;
        
        // Safer Loop to count months
        $current = $firstMonthEnd->copy()->addDay(); // 1st of next month
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
        
        // We calculate total exemptions using the exact same logic as Annual Gross
        // 1. Partial First Month Exemption
        $daysInFirstMonth = $joiningDate->daysInMonth;
        $workedDays = $daysInFirstMonth - $joiningDate->day + 1;
        $firstMonthRatio = $joiningDate->day == 1 ? 1.0 : ($workedDays / $daysInFirstMonth);
        
        // 2. Remaining Full Months
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
                
                // Exemption for first partial month
                $firstMonthExemption = $monthlyExemptLimit * $firstMonthRatio;
                // Exemption for remaining full months
                $remainingExemption = $monthlyExemptLimit * $remainingMonthsCount;
                
                $totalExemption += ($firstMonthExemption + $remainingExemption);
            }
        }

        return max($annualGross - $totalExemption, 0.0);
    }

    private function calculateFromAnnualGross(float $annualTaxableIncome, ?TaxRate $taxRateRecord): float
    {
        if ($annualTaxableIncome <= 0 || !$taxRateRecord) return 0.0;

        $slabs = $taxRateRecord->slabs ?? [];
        usort($slabs, fn ($a, $b) => ($a['income_from'] ?? 0) <=> ($b['income_from'] ?? 0));

        $tax = 0.0;
        foreach ($slabs as $slab) {
            $from = (float) ($slab['income_from'] ?? 0);
            if ($annualTaxableIncome >= $from) {
                $fixed = (float) ($slab['fixed_tax_amount'] ?? 0);
                $rate  = (float) ($slab['tax_rate_percentage'] ?? 0);
                $tax = $fixed + (($annualTaxableIncome - $from) * $rate / 100);
            }
        }
        return round($tax, 2);
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