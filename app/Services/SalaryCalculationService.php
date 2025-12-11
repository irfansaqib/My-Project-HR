<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Models\SalaryComponent;
use App\Models\Loan;
use App\Models\Fund;
use App\Models\LeaveEncashment;
use App\Models\SalarySheetItem; // ✅ Added
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // ✅ Added

class SalaryCalculationService
{
    public function calculateForMonth(Employee $employee, Carbon $payrollDate): array
    {
        // 1. Month Boundaries
        $monthStart = $payrollDate->copy()->startOfMonth()->startOfDay();
        $monthEnd = $payrollDate->copy()->endOfMonth()->startOfDay();
        $daysInMonth = $monthStart->diffInDays($monthEnd) + 1;

        // 2. Find Structures
        $initialStructure = SalaryStructure::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('effective_date', '<', $monthStart)
            ->orderBy('effective_date', 'desc')
            ->first();
        
        $midMonthRevisions = SalaryStructure::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('effective_date', [$monthStart, $payrollDate->copy()->endOfMonth()->endOfDay()])
            ->orderBy('effective_date', 'asc')
            ->get();
        
        if (!$initialStructure && $midMonthRevisions->isEmpty()) {
            return $this->emptyData();
        }

        // 3. Build Periods
        $periods = [];
        if ($initialStructure) {
            $periods[] = ['structure' => $initialStructure, 'start' => $monthStart->copy(), 'end' => null];
        }

        foreach ($midMonthRevisions as $revision) {
            $effDate = $revision->effective_date->copy()->startOfDay();
            if (count($periods) > 0) {
                $periods[count($periods) - 1]['end'] = $effDate->copy()->subDay()->startOfDay();
            }
            $periods[] = ['structure' => $revision, 'start' => $effDate, 'end' => null];
        }

        if (count($periods) > 0) {
            if (is_null($periods[count($periods) - 1]['end'])) {
                $periods[count($periods) - 1]['end'] = $monthEnd->copy();
            }
        }
        
        // --- 4. PREPARE DATA ---
        $funds = Fund::where('business_id', $employee->business_id)->get()->keyBy('salary_component_id');
        $fundContributions = []; 

        $totalProratedBasic = 0;
        $totalProratedAllowances = 0;
        $totalProratedDeductions = 0;
        $allowanceBreakdown = [];
        $deductionBreakdown = [];

        $joiningDate = $employee->joining_date ? Carbon::parse($employee->joining_date)->startOfDay() : null;
        $exitDate = $employee->exit_date ? Carbon::parse($employee->exit_date)->startOfDay() : null;

        foreach ($periods as $period) {
            $pStart = $period['start'];
            $pEnd = $period['end'];

            if ($pStart->lt($monthStart)) $pStart = $monthStart->copy();
            if ($pEnd->gt($monthEnd)) $pEnd = $monthEnd->copy();

            if ($joiningDate && $joiningDate->gt($pStart)) $pStart = $joiningDate->copy();
            if ($exitDate && $exitDate->lt($pEnd)) $pEnd = $exitDate->copy();

            if ($pStart->gt($pEnd)) continue;

            $daysInPeriod = $pStart->diffInDays($pEnd) + 1;
            $prorationFactor = ($daysInPeriod >= $daysInMonth) ? 1.0 : ($daysInPeriod / $daysInMonth);

            $structure = $period['structure'];
            $rawComponents = $structure->salary_components;
            if (is_string($rawComponents)) $rawComponents = json_decode($rawComponents, true);
            $components = collect($rawComponents ?? []);

            $periodBasic = (float)$structure->basic_salary * $prorationFactor;
            $totalProratedBasic += $periodBasic;

            foreach ($components as $comp) {
                if (!is_array($comp)) continue;
                $amount = (float)($comp['amount'] ?? 0);
                $proratedAmount = $amount * $prorationFactor;
                $name = $comp['name'] ?? 'Unknown';
                $compId = $comp['id'] ?? null;
                $type = $comp['type'] ?? 'allowance';

                if ($type === 'allowance') {
                    $totalProratedAllowances += $proratedAmount;
                    if (!isset($allowanceBreakdown[$name])) $allowanceBreakdown[$name] = 0;
                    $allowanceBreakdown[$name] += $proratedAmount;
                } else {
                    $totalProratedDeductions += $proratedAmount;
                    if (!isset($deductionBreakdown[$name])) $deductionBreakdown[$name] = 0;
                    $deductionBreakdown[$name] += $proratedAmount;

                    if ($compId && isset($funds[$compId])) {
                        $fund = $funds[$compId];
                        
                        $employerShare = 0;
                        if ($fund->employer_contribution_type === 'match_employee') {
                            $employerShare = $proratedAmount;
                        } elseif ($fund->employer_contribution_type === 'percentage_of_basic') {
                            $employerShare = $periodBasic * ($fund->employer_contribution_value / 100);
                        } elseif ($fund->employer_contribution_type === 'fixed_amount') {
                            $employerShare = $fund->employer_contribution_value * $prorationFactor;
                        }

                        if (!isset($fundContributions[$fund->id])) {
                            $fundContributions[$fund->id] = [
                                'fund_id' => $fund->id,
                                'fund_name' => $fund->name,
                                'employee_share' => 0,
                                'employer_share' => 0
                            ];
                        }
                        $fundContributions[$fund->id]['employee_share'] += $proratedAmount;
                        $fundContributions[$fund->id]['employer_share'] += $employerShare;
                    }
                }
            }
        }

        // 5. Bonuses
        $monthlyBonus = (float) $employee->incentives
            ->where('type', 'bonus')
            ->whereBetween('effective_date', [$monthStart, $payrollDate->copy()->endOfMonth()->endOfDay()])
            ->sum('increment_amount');

        // 6. LEAVE ENCASHMENT
        $encashments = LeaveEncashment::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereNull('salary_sheet_item_id')
            ->get();

        $totalEncashmentAmount = $encashments->sum('amount');
        $encashmentIds = $encashments->pluck('id')->toArray();

        // 7. LOAN DEDUCTIONS
        $loanComponent = SalaryComponent::where('business_id', $employee->business_id)->where('is_loan', true)->first();
        $advanceComponent = SalaryComponent::where('business_id', $employee->business_id)->where('is_advance', true)->first();
        
        $activeLoans = Loan::where('employee_id', $employee->id)
            ->where('status', 'running')
            ->where('repayment_start_date', '<=', $monthEnd)
            ->get();

        foreach($activeLoans as $loan) {
            $pending = $loan->total_amount - $loan->recovered_amount;
            if ($pending <= 0) continue;

            $deduct = 0;
            $compName = null;

            if ($loan->type === 'advance' && $advanceComponent) {
                $deduct = $pending;
                $compName = $advanceComponent->name;
            } elseif (($loan->type === 'loan' || $loan->type === 'fund_loan') && $loanComponent) {
                $deduct = min($loan->installment_amount, $pending);
                $compName = $loanComponent->name;
            }

            if ($deduct > 0) {
                $totalProratedDeductions += $deduct;
                $displayName = $compName ?? 'Loan Deduction';
                if (!isset($deductionBreakdown[$displayName])) $deductionBreakdown[$displayName] = 0;
                $deductionBreakdown[$displayName] += $deduct;
            }
        }
        
        // 8. CALCULATE ARREARS (Previous Unpaid Amounts)
        // Logic: Find previous sheet items for this employee where (Payable > Paid) AND Status is NOT Held
        // We exclude 'held' because held salary is explicitly stopped, not just "unpaid".
        $previousUnpaid = SalarySheetItem::where('employee_id', $employee->id)
            ->whereHas('salarySheet', function($q) use ($monthStart) {
                $q->where('month', '<', $monthStart); // Strictly previous months
            })
            ->where('payment_status', '!=', 'paid') // Not fully paid
            ->where('payment_status', '!=', 'held') // Held salaries stay held until released
            ->get()
            ->sum(function($item) {
                return $item->payable_amount - $item->paid_amount;
            });

        // 9. Final Calculation
        $finalGrossSalary = $totalProratedBasic + $totalProratedAllowances + $monthlyBonus + $totalEncashmentAmount;

        return [
            'basic_salary' => round($totalProratedBasic, 2),
            'gross_salary' => round($finalGrossSalary, 2),
            'bonus' => round($monthlyBonus, 2),
            'leave_encashment_amount' => round($totalEncashmentAmount, 2),
            'encashment_ids' => $encashmentIds,
            'total_deductions_components' => round($totalProratedDeductions, 2),
            'arrears_adjustment' => round($previousUnpaid, 2), // ✅ Send Arrears separately
            'allowances_breakdown' => $allowanceBreakdown, 
            'deductions_breakdown' => $deductionBreakdown,
            'fund_contributions' => $fundContributions,
        ];
    }
    
    private function emptyData() {
        return [
            'basic_salary' => 0, 'gross_salary' => 0, 'bonus' => 0, 
            'leave_encashment_amount' => 0, 'encashment_ids' => [],
            'arrears_adjustment' => 0,
            'total_deductions_components' => 0,
            'allowances_breakdown' => [], 'deductions_breakdown' => [], 'fund_contributions' => []
        ];
    }
}