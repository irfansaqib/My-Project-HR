<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaxRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TaxCalculatorService
{
    public function calculate(Employee $employee, Carbon $payrollDate): float
    {
        $taxRateRecord = TaxRate::where('business_id', Auth::user()->business_id)
            ->where('effective_from_date', '<=', $payrollDate)
            ->where(function ($query) use ($payrollDate) {
                $query->where('effective_to_date', '>=', $payrollDate)
                      ->orWhereNull('effective_to_date');
            })
            ->first();

        if (!$taxRateRecord || !is_array($taxRateRecord->slabs) || empty($taxRateRecord->slabs)) {
            return 0;
        }

        $slabs = $taxRateRecord->slabs;
        $annualGrossSalary = (float) $employee->gross_salary * 12;
        $totalAnnualExemption = 0;
        
        $exemptAllowances = $employee->salaryComponents()->where('is_tax_exempt', true)->get();
        foreach ($exemptAllowances as $allowance) {
            if ($allowance->exemption_type === 'percentage_of_basic' && isset($allowance->exemption_value)) {
                $monthlyExemption = ((float) $employee->basic_salary * (float) $allowance->exemption_value) / 100;
                $totalAnnualExemption += $monthlyExemption * 12;
            }
        }

        $annualTaxableIncome = $annualGrossSalary - $totalAnnualExemption;

        if ($annualTaxableIncome < 0) { return 0; }

        $correctSlab = null;
        usort($slabs, function ($a, $b) {
            return (float) ($b['income_from'] ?? 0) <=> (float) ($a['income_from'] ?? 0);
        });

        foreach ($slabs as $slab) {
            if ($annualTaxableIncome >= (float) ($slab['income_from'] ?? 0)) {
                $correctSlab = $slab;
                break;
            }
        }
        
        if (!$correctSlab) { return 0; }

        $taxableAmountAboveBase = $annualTaxableIncome - (float) $correctSlab['income_from'];
        $rateTax = ($taxableAmountAboveBase * (float) $correctSlab['tax_rate_percentage']) / 100;
        $annualTax = (float) $correctSlab['fixed_tax_amount'] + $rateTax;

        // --- SURCHARGE LOGIC CORRECTION ---
        $surchargeThreshold = (float) $taxRateRecord->surcharge_threshold;
        $surchargeRate = (float) $taxRateRecord->surcharge_rate_percentage;

        if ($surchargeThreshold > 0 && $surchargeRate > 0 && $annualTaxableIncome > $surchargeThreshold) {
             // Surcharge is often a percentage of the income within the surcharge bracket, not the tax itself.
             // This is an interpretation; tax law can vary. Let's assume it's a simple additional tax on the total tax.
             $surchargeAmount = ($annualTax * $surchargeRate) / 100;
             $annualTax += $surchargeAmount;
        }

        $monthlyTax = $annualTax / 12;

        return round($monthlyTax, 2);
    }
}