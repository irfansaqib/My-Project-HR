<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalaryStructure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SalaryCalculationService
{
    /**
     * Calculates the historically accurate and prorated salary for a specific month using approved snapshots.
     */
    public function calculateForMonth(Employee $employee, Carbon $payrollDate): array
    {
        $monthStart = $payrollDate->copy()->startOfMonth();
        $monthEnd = $payrollDate->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;

        // --- 1. FIND ALL APPROVED SALARY STRUCTURES RELEVANT TO THIS MONTH ---
        
        // Find the structure that was active at the very start of the month
        $initialStructure = SalaryStructure::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('effective_date', '<', $monthStart)
            ->orderBy('effective_date', 'desc')
            ->first();
        
        // Find all new structures that become effective within the month
        $midMonthRevisions = SalaryStructure::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('effective_date', [$monthStart, $monthEnd])
            ->orderBy('effective_date', 'asc')
            ->get();
        
        // If there are no approved structures at all, we can't calculate a salary.
        if (!$initialStructure && $midMonthRevisions->isEmpty()) {
            return [ 'basic_salary' => 0, 'gross_salary' => 0, 'bonus' => 0, 'total_deductions_components' => 0 ];
        }

        // --- 2. CREATE A TIMELINE OF SALARY PERIODS FOR THE MONTH ---
        $periods = collect();
        $lastDate = $monthStart->copy()->subDay();

        if ($initialStructure) {
            $periods->push(['structure' => $initialStructure, 'start_date' => $monthStart->copy()]);
        }

        foreach ($midMonthRevisions as $revision) {
            // If there was a gap between the last date and this revision, fill it with the last known structure
            if ($periods->isNotEmpty() && $lastDate->diffInDays($revision->effective_date) > 1) {
                $periods->last()['end_date'] = $revision->effective_date->copy()->subDay();
            }
            $periods->push(['structure' => $revision, 'start_date' => $revision->effective_date->copy()]);
            $lastDate = $revision->effective_date->copy();
        }

        // Set the end date for the very last period
        if ($periods->isNotEmpty()) {
            $periods->last()['end_date'] = $monthEnd->copy();
        }
        
        // --- 3. LOOP THROUGH EACH PERIOD AND CALCULATE PRORATED SALARY ---
        $totalProratedBasic = 0;
        $totalProratedAllowances = 0;
        $totalProratedDeductions = 0;

        foreach ($periods as $period) {
            $daysInPeriod = $period['start_date']->diffInDays($period['end_date']) + 1;
            $prorationFactor = $daysInPeriod / $daysInMonth;

            $structure = $period['structure'];
            $components = collect($structure->salary_components);

            $totalProratedBasic += (float)$structure->basic_salary * $prorationFactor;
            $totalProratedAllowances += $components->where('type', 'allowance')->sum('amount') * $prorationFactor;
            $totalProratedDeductions += $components->where('type', 'deduction')->sum('amount') * $prorationFactor;
        }

        // --- 4. CALCULATE ONE-TIME BONUSES (NOT PRORATED) ---
        $monthlyBonus = (float) $employee->incentives
            ->where('type', 'bonus')
            ->whereBetween('effective_date', [$monthStart, $monthEnd])
            ->sum('increment_amount');

        // --- 5. FINAL CALCULATION ---
        $finalGrossSalary = $totalProratedBasic + $totalProratedAllowances + $monthlyBonus;

        return [
            'basic_salary' => round($totalProratedBasic, 2),
            'gross_salary' => round($finalGrossSalary, 2),
            'bonus' => round($monthlyBonus, 2),
            'total_deductions_components' => round($totalProratedDeductions, 2),
        ];
    }
}