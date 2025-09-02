<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaxRate; // Corrected from TaxSlab
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TaxCalculatorService
{
    public function calculate(Employee $employee, Carbon $payrollDate): float
    {
        // Using the correct TaxRate model
        $taxRateSet = TaxRate::where('business_id', Auth::user()->business_id)
            ->where('effective_from_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->where('effective_to_date', '>=', $payrollDate)
                      ->orWhereNull('effective_to_date');
            })
            // Use orderBy income_from in descending order to check higher rates first
            ->orderBy('income_from', 'desc') 
            ->get();

        if ($taxRateSet->isEmpty()) {
            return 0;
        }

        // 1. Annualize the gross salary
        $annualGrossSalary = $employee->gross_salary * 12;

        // 2. Calculate total annual exemptions
        $totalAnnualExemption = 0;
        $exemptAllowances = $employee->salaryComponents()->where('is_tax_exempt', true)->get();
        
        foreach ($exemptAllowances as $allowance) {
            if ($allowance->exemption_type === 'percentage_of_basic') {
                // Annualize the exemption
                $monthlyExemption = ($employee->basic_salary * $allowance->exemption_value) / 100;
                $totalAnnualExemption += $monthlyExemption * 12;
            }
        }

        // 3. Calculate annual taxable income
        $annualTaxableIncome = $annualGrossSalary - $totalAnnualExemption;

        if ($annualTaxableIncome <= 0) {
            return 0;
        }
        
        // 4. Find the correct tax rate for the annual taxable income
        $rate = null;
        foreach ($taxRateSet as $r) {
            // Since we ordered by income_from descending, the first match will be the correct rate
            if ($annualTaxableIncome >= $r->income_from) {
                $rate = $r;
                break;
            }
        }

        if (!$rate) {
            // This case would typically handle incomes below the first taxable bracket
            return 0;
        }

        // 5. Calculate the total annual tax based on the rate
        $taxableAmountAboveBase = $annualTaxableIncome - $rate->income_from;
        $rateTax = ($taxableAmountAboveBase * $rate->tax_rate_percentage) / 100;
        $annualTax = $rate->fixed_tax_amount + $rateTax;

        // Apply surcharge if applicable
        if ($rate->surcharge_threshold > 0 && $rate->surcharge_rate_percentage > 0 && $annualTaxableIncome > $rate->surcharge_threshold) {
            $surchargeAmount = ($annualTax * $rate->surcharge_rate_percentage) / 100;
            $annualTax += $surchargeAmount;
        }

        // 6. The monthly tax is simply the annual tax divided by 12
        $monthlyTax = $annualTax / 12;

        return round($monthlyTax, 2);
    }
}