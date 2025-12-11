<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BulkTaxCalculatorController extends Controller
{
    protected $taxCalculator;

    public function __construct(TaxCalculatorService $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

    public function index()
    {
        $allowances = SalaryComponent::where('business_id', auth()->user()->business_id)
            ->where('type', 'allowance')
            ->get();

        $currentYear = Carbon::now()->year;
        $taxYears = [];
        for ($i = -1; $i <= 1; $i++) {
            $y = $currentYear + $i;
            $taxYears[$y] = "Tax Year " . ($y - 1) . "-" . $y;
        }

        return view('tools.bulk_tax_calculator', compact('allowances', 'taxYears'));
    }

    public function downloadTemplate()
    {
        $allowances = SalaryComponent::where('business_id', auth()->user()->business_id)
            ->where('type', 'allowance')
            ->pluck('name')
            ->toArray();

        $columns = array_merge(
            [
                'CNIC', 'Employee Name', 'Joining Date (YYYY-MM-DD)', 
                'Current Monthly Basic', 'One-Time Bonus',
                'YTD Income (Optional)', 'Tax Deducted YTD (Optional)', 'Months Passed (Optional)'
            ], 
            $allowances
        );

        $sample = array_merge(
            ['35202-1234567-1', 'John Doe', date('Y-m-d'), '100000', '0', '0', '0', '0'],
            array_fill(0, count($allowances), '0')
        );

        $callback = function() use ($columns, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $sample);
            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Bulk_Tax_Template.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream($callback, 200, $headers);
    }

    public function process(Request $request)
    {
        $request->validate([
            'tax_year' => 'required|integer|min:2020|max:2030',
        ]);

        $taxYear = (int) $request->tax_year;
        $taxDate = Carbon::create($taxYear, 6, 30);
        
        $data = [];
        $allowanceModels = SalaryComponent::where('business_id', auth()->user()->business_id)
            ->where('type', 'allowance')
            ->get()
            ->keyBy('name');

        // --- HELPER CLOSURE ---
        $calculateRow = function($basic, $bonus, $ytdIncome, $taxPaidYtd, $monthsPassed, $allowanceInputs) use ($allowanceModels, $taxDate, $taxYear) {
            
            // 1. Monthly Figures
            $currentMonthlyGross = $basic;
            $currentMonthlyTaxable = $basic;
            $totalMonthlyAllowances = 0;

            foreach ($allowanceModels as $comp) {
                $amount = (float) ($allowanceInputs[$comp->name] ?? 0);
                if ($amount <= 0) continue;

                $totalMonthlyAllowances += $amount;
                $currentMonthlyGross += $amount;

                if ($comp->is_tax_exempt) {
                    $exemptAmount = 0;
                    if ($comp->exemption_type == 'percentage_of_basic') {
                        $exemptAmount = $basic * ($comp->exemption_value / 100);
                    } elseif ($comp->exemption_type == 'fixed_amount') {
                        $exemptAmount = $comp->exemption_value;
                    }
                    $currentMonthlyTaxable += max(0, $amount - $exemptAmount);
                } else {
                    $currentMonthlyTaxable += $amount;
                }
            }

            // 2. Timeline
            $monthsPassed = max(0, min(11, (int)$monthsPassed));
            $monthsRemaining = 12 - $monthsPassed;

            // 3. Annual Totals
            $annualGross = ($currentMonthlyGross * 12) + $bonus; 

            // 4. Tax Calculation
            $result = $this->taxCalculator->calculateReconciledTax(
                $currentMonthlyTaxable, 
                $ytdIncome, 
                $taxPaidYtd, 
                $monthsRemaining, 
                $bonus, 
                $taxDate
            );

            // 5. Generate Detailed Schedule
            $schedule = [];
            $startMonth = Carbon::create($taxYear - 1, 7, 1); 
            
            // History Rows
            for ($i = 0; $i < $monthsPassed; $i++) {
                $date = $startMonth->copy()->addMonths($i);
                $schedule[] = [
                    'month' => $date->format('M Y'),
                    'type'  => 'History',
                    'basic' => '-', 
                    'allowances' => '-',
                    'gross' => '-',
                    'taxable' => '-',
                    'tax'   => $taxPaidYtd > 0 ? round($taxPaidYtd / $monthsPassed) : 0
                ];
            }
            
            // Future Rows
            // ✅ FIX: Store RAW numbers here, do NOT use number_format()
            for ($i = 0; $i < $monthsRemaining; $i++) {
                $date = $startMonth->copy()->addMonths($monthsPassed + $i);
                $schedule[] = [
                    'month' => $date->format('M Y'),
                    'type'  => 'Projected',
                    'basic' => $basic,
                    'allowances' => $totalMonthlyAllowances,
                    'gross' => $currentMonthlyGross,
                    'taxable' => $currentMonthlyTaxable,
                    'tax'   => round($result['new_monthly_tax'])
                ];
            }

            // Bonus Row
            if($bonus > 0) {
                $schedule[] = [
                    'month' => 'One-Time Bonus',
                    'type' => 'Bonus',
                    'basic' => '-',
                    'allowances' => '-',
                    'gross' => $bonus,
                    'taxable' => $bonus,
                    'tax' => '-' 
                ];
            }

            return [
                'monthly_gross' => $currentMonthlyGross,
                'annual_gross' => $annualGross,
                'annual_taxable' => $result['annual_taxable'],
                'new_monthly_tax' => $result['new_monthly_tax'],
                'total_annual_tax' => $result['total_annual_tax'],
                'tax_paid_so_far' => $taxPaidYtd,
                'schedule' => $schedule
            ];
        };

        // --- SCENARIO A: Excel Upload ---
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);

            foreach ($csvData as $row) {
                if(count($header) != count($row)) continue;
                $rowMap = array_combine($header, $row);
                
                $basic = (float) ($rowMap['Current Monthly Basic'] ?? $rowMap['Monthly Basic Salary'] ?? 0);
                $bonus = (float) ($rowMap['One-Time Bonus'] ?? 0);
                $ytdIncome = (float) ($rowMap['YTD Income (Optional)'] ?? 0);
                $taxPaidYtd = (float) ($rowMap['Tax Deducted YTD (Optional)'] ?? 0);
                $monthsPassed = (int) ($rowMap['Months Passed (Optional)'] ?? 0);

                $result = $calculateRow($basic, $bonus, $ytdIncome, $taxPaidYtd, $monthsPassed, $rowMap);
                
                $data[] = array_merge([
                    'cnic' => $rowMap['CNIC'] ?? '-',
                    'name' => $rowMap['Employee Name'] ?? '-',
                    'basic' => $basic,
                    'bonus' => $bonus,
                ], $result);
            }
        } 
        
        // --- SCENARIO B: Manual Entry ---
        elseif ($request->has('manual_data')) {
            foreach ($request->manual_data as $entry) {
                $basic = (float) $entry['basic'];
                $bonus = (float) ($entry['bonus'] ?? 0);
                $ytdIncome = (float) ($entry['ytd_income'] ?? 0);
                $taxPaidYtd = (float) ($entry['tax_paid_ytd'] ?? 0);
                $monthsPassed = (int) ($entry['months_passed'] ?? 0);

                $allowanceInputs = [];
                if(isset($entry['allowances'])) {
                    foreach($entry['allowances'] as $id => $amount) {
                        $compName = $allowanceModels->where('id', $id)->first()->name ?? '';
                        if($compName) $allowanceInputs[$compName] = $amount;
                    }
                }

                $result = $calculateRow($basic, $bonus, $ytdIncome, $taxPaidYtd, $monthsPassed, $allowanceInputs);

                $data[] = array_merge([
                    'cnic' => $entry['cnic'],
                    'name' => $entry['name'],
                    'basic' => $basic,
                    'bonus' => $bonus,
                ], $result);
            }
        }

        return view('tools.bulk_tax_result', compact('data'));
    }
}