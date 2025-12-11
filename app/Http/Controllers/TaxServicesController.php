<?php

namespace App\Http\Controllers;

use App\Models\TaxClient;
use App\Models\TaxClientEmployee;
use App\Models\TaxClientSalarySheet;
use App\Models\TaxClientSalaryItem;
use App\Models\TaxClientSalaryComponent;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaxServicesController extends Controller
{
    protected $taxCalculator;

    public function __construct(TaxCalculatorService $taxCalculator)
    {
        $this->taxCalculator = $taxCalculator;
    }

    // =============================================================================================
    // 1. CLIENT MANAGEMENT & TABS
    // =============================================================================================
    
    public function index()
    {
        $clients = TaxClient::where('business_id', Auth::user()->business_id)
            ->withCount('employees')
            ->get();
        return view('tax-services.index', compact('clients'));
    }

    public function storeClient(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        
        TaxClient::create([
            'business_id' => Auth::user()->business_id,
            'name' => $request->name,
            'ntn' => $request->ntn,
            'contact_person' => $request->contact_person,
            'is_onboarded' => false
        ]);
        
        return back()->with('success', 'Client added successfully.');
    }

    public function tabEmployees(TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        $client->load(['employees' => function($q) {
            $q->orderByRaw("FIELD(status, 'active', 'resigned')");
        }]);
        return view('tax-services.tabs.employees', compact('client'));
    }

    public function tabComponents(TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        // Custom Sort: Basic Salary (1) -> Allowances (2) -> Deductions (3)
        $client->load(['components' => function($q) {
            $q->orderByRaw("
                CASE 
                    WHEN name = 'Basic Salary' THEN 1 
                    WHEN type = 'allowance' THEN 2 
                    ELSE 3 
                END ASC
            ");
        }]);
        return view('tax-services.tabs.components', compact('client'));
    }

    public function tabSalary(TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        
        $client->load(['salarySheets' => function($q) {
            $q->orderBy('month', 'desc');
        }]);
        
        $lastRecord = $client->salarySheets->first(); 
        $hasPendingDraft = false;
        $nextPayrollMonth = null;

        if ($lastRecord) {
            if ($lastRecord->status === 'draft') {
                $hasPendingDraft = true;
                $nextPayrollMonth = Carbon::parse($lastRecord->month);
            } else {
                $nextPayrollMonth = Carbon::parse($lastRecord->month)->addMonth();
            }
        } elseif ($client->is_onboarded && $client->payroll_start_month) {
            $nextPayrollMonth = Carbon::parse($client->payroll_start_month);
        }

        return view('tax-services.tabs.salary_index', compact('client', 'nextPayrollMonth', 'hasPendingDraft'));
    }

    // --- UPDATED REPORT TAB (SORTED NEWEST FIRST) ---
    public function tabReports(TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        
        // Load relationships sorted by Month DESC (Newest First)
        $client->load(['salarySheets' => function($q) {
            $q->where('status', 'finalized')->orderBy('month', 'desc');
        }]);

        // Pass the sorted sheets to the view for the dropdown as well
        $salarySheets = $client->salarySheets;

        return view('tax-services.tabs.reports', compact('client', 'salarySheets'));
    }

    public function updateClientSettings(Request $request, TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        $client->update($request->all());
        return back()->with('success', 'Settings updated.'); 
    }

    public function unlockOnboarding($clientId)
    {
        $client = TaxClient::findOrFail($clientId);
        if ($client->business_id !== Auth::user()->business_id) abort(403);

        if ($client->salarySheets()->exists()) {
            return back()->with('error', 'Cannot unlock onboarding because payroll sheets exist. Delete sheets first.');
        }

        $client->update([
            'is_onboarded' => false, 
            'payroll_start_month' => null,
            'saved_salary_months' => null
        ]);
        
        return back()->with('success', 'Onboarding unlocked. You can now edit Opening History.');
    }

    // =============================================================================================
    // 2. COMPONENTS
    // =============================================================================================
    
    public function storeComponent(Request $request, TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);

        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:allowance,deduction',
            'is_tax_exempt' => 'required|boolean',
            'exemption_type' => 'nullable|required_if:is_tax_exempt,1|in:percentage_of_basic,fixed_amount',
            'exemption_value' => 'nullable|required_if:is_tax_exempt,1|numeric',
        ]);

        TaxClientSalaryComponent::create([
            'tax_client_id' => $client->id,
            'name' => $request->name,
            'type' => $request->type,
            'is_tax_exempt' => $request->is_tax_exempt,
            'exemption_type' => $request->exemption_type,
            'exemption_value' => $request->exemption_value,
        ]);

        return back()->with('success', 'Component added successfully.');
    }

    public function destroyComponent(TaxClient $client, $componentId)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        
        TaxClientSalaryComponent::where('tax_client_id', $client->id)
                     ->where('id', $componentId)
                     ->firstOrFail()
                     ->delete();

        return back()->with('success', 'Component removed successfully.');
    }

    public function updateComponent(Request $request, TaxClient $client, $componentId)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:allowance,deduction',
            'is_tax_exempt' => 'required|boolean',
            'exemption_type' => 'nullable|required_if:is_tax_exempt,1|in:percentage_of_basic,fixed_amount',
            'exemption_value' => 'nullable|required_if:is_tax_exempt,1|numeric',
        ]);

        $component = TaxClientSalaryComponent::where('tax_client_id', $client->id)
                     ->where('id', $componentId)
                     ->firstOrFail();

        // Protect Basic Salary
        if ($component->name === 'Basic Salary') {
            $component->update([
                'is_tax_exempt' => false,
                'exemption_type' => null,
                'exemption_value' => null
            ]);
        } else {
            $component->update([
                'name' => $request->name,
                'type' => $request->type,
                'is_tax_exempt' => $request->is_tax_exempt,
                'exemption_type' => $request->exemption_type,
                'exemption_value' => $request->exemption_value,
            ]);
        }

        return back()->with('success', 'Component updated successfully.');
    }

    // =============================================================================================
    // 3. EMPLOYEE MANAGEMENT
    // =============================================================================================

    public function storeEmployee(Request $request, TaxClient $client)
    {
        $request->validate([
            'name' => 'required',
            'cnic' => 'required',
            'designation' => 'nullable|string',
            'joining_date' => 'required|date',
        ]);

        TaxClientEmployee::create([
            'tax_client_id' => $client->id,
            'name' => $request->name,
            'cnic' => $request->cnic,
            'designation' => $request->designation,
            'joining_date' => $request->joining_date,
            'status' => 'active',
            'current_basic_salary' => 0,
            'current_bonus' => 0,
            'opening_gross_salary' => 0,
            'opening_taxable_income' => 0,
            'opening_tax_paid' => 0,
        ]);

        return back()->with('success', 'Employee added successfully.');
    }

    public function deleteEmployee(Request $request, $clientId, $employeeId)
    {
        $employee = TaxClientEmployee::where('id', $employeeId)->where('tax_client_id', $clientId)->firstOrFail();

        if ($request->has('exit_date') && $request->exit_date != null) {
            $request->validate(['exit_date' => 'required|date']);
            $employee->update([
                'status' => 'resigned',
                'exit_date' => $request->exit_date
            ]);
            return back()->with('success', 'Employee marked as resigned on ' . $request->exit_date);
        } 
        
        if ($employee->salaryItems()->doesntExist()) {
             $employee->delete();
             return back()->with('success', 'Employee deleted successfully.');
        }

        return back()->with('error', 'Cannot delete employee with payroll history. Please use Exit Date instead.');
    }

    public function exportEmployees(TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        $fileName = 'Employees_' . str_replace(' ', '_', $client->name) . '.csv';
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache"];

        $callback = function() use($client) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'CNIC', 'Designation', 'Joining Date', 'Status']);
            foreach ($client->employees as $emp) {
                fputcsv($file, [$emp->name, $emp->cnic, $emp->designation, $emp->joining_date ? $emp->joining_date->format('Y-m-d') : '', $emp->status]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function importEmployees(Request $request, TaxClient $client)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $data = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $header = array_shift($data); 
        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                if (count($row) < 2) continue; 
                TaxClientEmployee::updateOrCreate(
                    ['tax_client_id' => $client->id, 'cnic' => $row[1]], 
                    ['name' => $row[0], 'designation' => $row[2] ?? null, 'joining_date' => $row[3] ?? null, 'status' => 'active']
                );
            }
            DB::commit();
            return back()->with('success', "Employees imported successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import Failed: ' . $e->getMessage());
        }
    }

    // =============================================================================================
    // 4. ONBOARDING & BULK UPDATE
    // =============================================================================================

    public function bulkUpdateEmployeeSalary(Request $request, $clientId)
    {
        return $this->saveSalaryDraft($request, $clientId); 
    }

    // =============================================================================================
    // 5. MONTHLY INPUT: DRAFT & FINALIZE
    // =============================================================================================

    public function saveSalaryDraft(Request $request, $clientId)
    {
        return $this->processSalarySave($request, $clientId, 'draft');
    }

    public function finalizeSalaryInput(Request $request, $clientId)
    {
        return $this->processSalarySave($request, $clientId, 'finalized');
    }

    private function processSalarySave(Request $request, $clientId, $status)
    {
        $client = TaxClient::findOrFail($clientId);
        $monthStr = $request->input('context_month');
        $monthDate = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth();
        $employeesData = $request->input('employees', []);

        DB::beginTransaction();
        try {
            $sheet = TaxClientSalarySheet::firstOrCreate(
                ['tax_client_id' => $client->id, 'month' => $monthDate],
                ['status' => 'draft']
            );

            if($sheet->status == 'finalized' && $status == 'draft') {
                return response()->json(['status'=>'error', 'message'=>'Cannot edit finalized record.'], 403);
            }

            $this->saveItemsLogic($client, $sheet, $employeesData);

            if($status == 'finalized') {
                $sheet->update(['status' => 'finalized']);
                $savedMonths = $client->saved_salary_months ?? [];
                if (!in_array($monthStr, $savedMonths)) {
                    $savedMonths[] = $monthStr;
                    $client->update(['saved_salary_months' => $savedMonths]);
                }
            }
            
             // Mark Onboarded if this is first
            if (!$client->is_onboarded) {
                $client->update([
                    'is_onboarded' => true,
                    'payroll_start_month' => $monthDate,
                    'saved_salary_months' => array_unique(array_merge($client->saved_salary_months ?? [], [$monthStr]))
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => ($status == 'draft' ? 'Draft Saved' : 'Month Finalized Successfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function saveItemsLogic($client, $sheet, $data)
    {
        $fyStart = ($sheet->month->month >= 7) ? Carbon::create($sheet->month->year, 7, 1) : Carbon::create($sheet->month->year - 1, 7, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();
        
        $monthsDivisor = 0;
        $iter = $sheet->month->copy()->startOfMonth();
        while($iter->lte($fyEnd)) {
            $monthsDivisor++;
            $iter->addMonth();
        }
        if($monthsDivisor <= 0) $monthsDivisor = 1;

        $clientComponents = TaxClientSalaryComponent::where('tax_client_id', $client->id)->get()->keyBy('name');

        foreach ($data as $empId => $row) {
             $emp = TaxClientEmployee::find($empId);
             if (!$emp) continue;

             // Handle Opening Balances (Onboarding)
             if (isset($row['opening_gross_salary'])) $emp->opening_gross_salary = $row['opening_gross_salary'];
             if (isset($row['opening_taxable_income'])) $emp->opening_taxable_income = $row['opening_taxable_income'];
             if (isset($row['opening_tax_paid'])) $emp->opening_tax_paid = $row['opening_tax_paid'];
             $emp->save(); // Save immediately for calculation

             $history = TaxClientSalaryItem::where('tax_client_employee_id', $empId)
                ->whereHas('sheet', function($q) use ($fyStart, $sheet) {
                    $q->where('status', 'finalized')->whereBetween('month', [$fyStart, $sheet->month->copy()->subDay()]);
                })
                ->selectRaw('SUM(taxable_income_monthly) as total_income, SUM(income_tax) as total_tax_paid')->first();

             $ytdTaxable = ($history->total_income ?? 0) + $emp->opening_taxable_income;
             $ytdTaxPaid = ($history->total_tax_paid ?? 0) + $emp->opening_tax_paid;

             $basic = (float)($row['current_basic_salary'] ?? 0);
             $bonus = (float)($row['current_bonus'] ?? 0);
             $allowances = $row['allowances'] ?? [];

             $fullGross = $basic;
             $fullTaxable = $basic;

             foreach($allowances as $name => $val) {
                 $val = (float)$val;
                 $fullGross += $val;
                 if (isset($clientComponents[$name]) && $clientComponents[$name]->is_tax_exempt) {
                     $exempt = ($clientComponents[$name]->exemption_type == 'percentage_of_basic') 
                            ? $basic * ($clientComponents[$name]->exemption_value / 100) 
                            : $clientComponents[$name]->exemption_value;
                     $fullTaxable += max(0, $val - $exempt);
                 } else {
                     $fullTaxable += $val;
                 }
             }

             $gross = $fullGross + $bonus;
             $taxable = $fullTaxable + $bonus;
             
             // Projection Logic
             $futureMonths = $monthsDivisor - 1;
             $futureTaxable = $fullTaxable * $futureMonths; 
             $estAnnual = $ytdTaxable + $taxable + $futureTaxable;
             
             $liability = $this->taxCalculator->calculateTaxFromAnnualIncome($estAnnual, $fyEnd);
             $remTax = max(0, $liability - $ytdTaxPaid);
             $sysTax = $remTax / $monthsDivisor;
             
             $manualTax = (isset($row['manual_tax_deduction']) && $row['manual_tax_deduction'] !== '') ? (float)$row['manual_tax_deduction'] : null;
             $actualTax = $manualTax !== null ? $manualTax : $sysTax;

             TaxClientSalaryItem::updateOrCreate(
                ['salary_sheet_id' => $sheet->id, 'tax_client_employee_id' => $empId],
                [
                    'basic_salary' => $basic,
                    'allowances_breakdown' => $allowances,
                    'bonus' => $bonus,
                    'gross_salary' => $gross,
                    'taxable_income_monthly' => $taxable,
                    'monthly_tax_chargeable' => $sysTax, 
                    'income_tax' => $actualTax, 
                    'net_salary' => $gross - $actualTax,
                    'taxable_income_ytd' => $ytdTaxable,
                    'tax_paid_ytd' => $ytdTaxPaid
                ]
             );
             
             $emp->update([
                 'current_basic_salary' => $basic,
                 'current_bonus' => 0, 
                 'current_allowances' => $allowances,
                 'manual_tax_deduction' => ($sheet->status == 'finalized') ? null : $manualTax 
             ]);
        }
    }

    // --- PREVIEW CALCULATION ---
    public function previewTaxCalculation(Request $request, $clientId)
    {
        $client = TaxClient::findOrFail($clientId);
        if ($client->business_id !== Auth::user()->business_id) abort(403);

        $month = Carbon::parse($request->context_month)->startOfMonth();
        $employeesData = $request->input('employees', []);
        
        $fyStart = ($month->month >= 7) ? Carbon::create($month->year, 7, 1) : Carbon::create($month->year - 1, 7, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();
        
        $monthsDivisor = 0;
        $iter = $month->copy()->startOfMonth();
        while($iter->lte($fyEnd)) {
            $monthsDivisor++;
            $iter->addMonth();
        }
        if($monthsDivisor <= 0) $monthsDivisor = 1;

        $clientComponents = TaxClientSalaryComponent::where('tax_client_id', $client->id)->get()->keyBy('name');
        $results = [];

        foreach ($employeesData as $empId => $data) {
            $emp = TaxClientEmployee::find($empId);
            if (!$emp) continue;

            $history = TaxClientSalaryItem::where('tax_client_employee_id', $empId)
                ->whereHas('sheet', function($q) use ($fyStart, $month) {
                    $q->where('status', 'finalized')->whereBetween('month', [$fyStart, $month->copy()->subDay()]);
                })
                ->selectRaw('SUM(gross_salary) as total_gross, SUM(taxable_income_monthly) as total_income, SUM(income_tax) as total_tax_paid')->first();

            $ytdTaxable = ($history->total_income ?? 0) + $emp->opening_taxable_income;
            $ytdTaxPaidActual = ($history->total_tax_paid ?? 0) + $emp->opening_tax_paid;

            $basic = (float)($data['current_basic_salary'] ?? 0);
            $bonus = (float)($data['current_bonus'] ?? 0);
            $allowancesInput = $data['allowances'] ?? [];

            $fullTaxable = $basic;
            $fullGross = $basic;

            foreach($allowancesInput as $name => $amount) {
                $amount = (float)$amount; 
                $fullGross += $amount;
                if (isset($clientComponents[$name]) && $clientComponents[$name]->is_tax_exempt) {
                    $exempt = ($clientComponents[$name]->exemption_type == 'percentage_of_basic') 
                           ? $basic * ($clientComponents[$name]->exemption_value / 100) 
                           : $clientComponents[$name]->exemption_value;
                    $fullTaxable += max(0, $amount - $exempt);
                } else {
                    $fullTaxable += $amount; 
                }
            }

            $currentGross = $fullGross + $bonus;
            $currentTaxable = $fullTaxable + $bonus;
            
            $futureMonths = $monthsDivisor - 1;
            $futureTaxable = $fullTaxable * $futureMonths; 
            $estAnnualTaxable = $ytdTaxable + $currentTaxable + $futureTaxable;
            
            $estAnnualTaxLiability = $this->taxCalculator->calculateTaxFromAnnualIncome($estAnnualTaxable, $fyEnd);
            $taxLiabilityRemaining = max(0, $estAnnualTaxLiability - $ytdTaxPaidActual);
            $systemMonthlyTaxChargeable = $taxLiabilityRemaining / $monthsDivisor;

            // Manual Override Priority Check
            $reqManual = isset($data['manual_tax_deduction']) && $data['manual_tax_deduction'] !== '' ? (float)$data['manual_tax_deduction'] : null;
            $dbManual = ($emp->manual_tax_deduction !== null) ? $emp->manual_tax_deduction : null;
            
            $finalTax = $reqManual;
            if ($finalTax === null) $finalTax = $dbManual;
            if ($finalTax === null) $finalTax = $systemMonthlyTaxChargeable;

            $results[$empId] = [
                'name' => $emp->name,
                'cnic' => $emp->cnic ?? 'N/A', 
                'current_basic' => $basic,
                'current_allowances' => $allowancesInput,
                'current_bonus' => $bonus,
                'current_gross' => $currentGross,
                'est_annual_tax' => $estAnnualTaxLiability,
                'tax_paid_ytd' => $ytdTaxPaidActual,
                'system_monthly_tax' => $systemMonthlyTaxChargeable, 
                'manual_tax_deduction' => $finalTax
            ];
        }
        return response()->json(['calculations' => $results]);
    }

    public function getMonthlyInputData(Request $request, TaxClient $client)
    {
        $month = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        $sheet = TaxClientSalarySheet::where('tax_client_id', $client->id)->where('month', $month)->with('items')->first();

        if(!$sheet) return response()->json(['status' => 'empty']);

        $data = [];
        foreach($sheet->items as $item) {
            $allowances = is_string($item->allowances_breakdown) ? json_decode($item->allowances_breakdown, true) : $item->allowances_breakdown;
            $data[$item->tax_client_employee_id] = [
                'basic' => $item->basic_salary,
                'bonus' => $item->bonus,
                'allowances' => $allowances,
                'manual_tax' => $item->income_tax 
            ];
        }
        return response()->json(['status' => 'found', 'data' => $data]);
    }

    // =============================================================================================
    // 6. GENERATE & SHOW SHEET
    // =============================================================================================

    public function generateSheet(Request $request, TaxClient $client)
    {
        return back(); 
    }

    public function showSheet(TaxClientSalarySheet $sheet)
    {
        $sheet->load(['items.employee']);
        
        // --- 1. Determine Tax Year & Context ---
        $month = Carbon::parse($sheet->month);
        $fyStart = ($month->month >= 7) ? Carbon::create($month->year, 7, 1) : Carbon::create($month->year - 1, 7, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();
        
        $monthsRemaining = 0;
        $iter = $month->copy()->addMonth()->startOfMonth();
        while($iter->lte($fyEnd)) {
            $monthsRemaining++;
            $iter->addMonth();
        }

        // --- 2. Fetch History (Up to Previous Month) ---
        $historyData = TaxClientSalaryItem::whereIn('tax_client_employee_id', $sheet->items->pluck('tax_client_employee_id'))
            ->whereHas('sheet', function($q) use ($fyStart, $month) {
                $q->where('status', 'finalized')
                  ->where('month', '>=', $fyStart)
                  ->where('month', '<', $month);
            })
            ->selectRaw('tax_client_employee_id, SUM(gross_salary) as total_gross, SUM(taxable_income_monthly) as total_taxable, SUM(income_tax) as total_tax')
            ->groupBy('tax_client_employee_id')
            ->get()
            ->keyBy('tax_client_employee_id');

        // --- 3. Loop & Calculate Projected Values for View ---
        foreach($sheet->items as $item) {
            $emp = $item->employee;
            $hist = $historyData[$emp->id] ?? null;

            // YTD (History + Opening + Current)
            $ytdGross = ($hist->total_gross ?? 0) + $emp->opening_gross_salary + $item->gross_salary;
            $ytdTaxable = ($hist->total_taxable ?? 0) + $emp->opening_taxable_income + $item->taxable_income_monthly;
            $ytdTaxPaid = ($hist->total_tax ?? 0) + $emp->opening_tax_paid + $item->income_tax;

            // Projection (Regular Monthly * Remaining)
            $regularMonthlyTaxable = $item->taxable_income_monthly - ($item->bonus ?? 0);
            $regularMonthlyGross = $item->gross_salary - ($item->bonus ?? 0);

            $projectedTaxable = $regularMonthlyTaxable * $monthsRemaining;
            $projectedGross = $regularMonthlyGross * $monthsRemaining;

            $estAnnualGross = $ytdGross + $projectedGross;
            $estAnnualTaxable = $ytdTaxable + $projectedTaxable;
            
            // Calculate Annual Tax
            $estAnnualTax = $this->taxCalculator->calculateTaxFromAnnualIncome($estAnnualTaxable, $fyEnd);

            // Inject into Item for View
            $item->display_est_annual_gross = $estAnnualGross;
            $item->display_est_annual_taxable = $estAnnualTaxable;
            $item->display_est_annual_tax = $estAnnualTax;
            $item->display_ytd_taxable = $ytdTaxable;
            $item->display_ytd_tax_paid = $ytdTaxPaid;
        }

        return view('tax-services.show_sheet', compact('sheet'));
    }

    public function finalizeSheet(TaxClientSalarySheet $sheet)
    {
        if ($sheet->status == 'finalized') return back();
        $sheet->update(['status' => 'finalized']);
        return back()->with('success', 'Sheet finalized.');
    }

    public function destroySheet(TaxClientSalarySheet $sheet)
    {
        if ($sheet->status == 'finalized') return back()->with('error', 'Cannot delete finalized sheets.');
        $sheet->delete();
        return back()->with('success', 'Draft deleted.');
    }

    // --- UPDATED EXPORT SHEET (WITH TOP HEADERS) ---
    public function exportSheet(TaxClientSalarySheet $sheet)
    {
        $sheet->load('items.employee');
        $fileName = 'Payroll_' . str_replace(' ', '_', $sheet->client->name) . '_' . $sheet->month->format('M_Y') . '.csv';
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache"];

        $mainHeaders = ['Employee', 'CNIC', 'Designation', 'Basic Salary'];
        $allowances = $sheet->client->components->where('type', 'allowance');
        foreach($allowances as $comp) $mainHeaders[] = $comp->name;
        array_push($mainHeaders, 'Bonus', 'Gross Salary', 'Taxable Income', 'Income Tax', 'Net Salary');

        $callback = function() use($sheet, $mainHeaders, $allowances) {
            $file = fopen('php://output', 'w');
            
            // Add Top-Level Headers
            fputcsv($file, [$sheet->client->name]);
            fputcsv($file, ['Monthly Payroll Sheet']);
            fputcsv($file, ['For the Month of ' . $sheet->month->format('F, Y')]);
            fputcsv($file, []); // Empty row separator

            fputcsv($file, $mainHeaders);
            foreach ($sheet->items as $item) {
                $row = [$item->employee->name, $item->employee->cnic, $item->employee->designation, $item->basic_salary];
                $breakdown = is_array($item->allowances_breakdown) ? $item->allowances_breakdown : json_decode($item->allowances_breakdown ?? '[]', true);
                foreach($allowances as $comp) { $row[] = $breakdown[$comp->name] ?? 0; }
                array_push($row, $item->bonus, $item->gross_salary, $item->taxable_income_monthly, $item->income_tax, $item->net_salary);
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    public function exportSalaryData(TaxClient $client) { /* Keep */ }
    public function importSalaryData(Request $request, TaxClient $client) { /* Keep */ }
    
    // --- UPDATED NEW YEAR & TAX YEAR LOGIC ---

    public function showNewYearForm(TaxClient $client) 
    { 
        $lastSheet = $client->salarySheets()
                            ->where('status', 'finalized')
                            ->orderBy('month', 'desc')
                            ->first();

        $canRollover = false;
        $nextTaxYearLabel = "N/A";
        $statusMessage = "";
        $lastMonthName = "None";

        if ($lastSheet) {
            $lastDate = Carbon::parse($lastSheet->month);
            $lastMonthName = $lastDate->format('F, Y');
            
            // Allow rollover ONLY if the last finalized month is June
            if ($lastDate->month == 6) {
                $canRollover = true;
                $nextTaxYearLabel = $lastDate->year . " - " . ($lastDate->year + 1);
            } else {
                $canRollover = false;
                $statusMessage = "Current Tax Year is not finished. You must finalize payroll up to June.";
            }
        } else {
            $canRollover = false;
            $statusMessage = "No finalized payrolls found. You must complete a Tax Year (ending in June) first.";
        }

        return view('tax-services.new_year_rollover', compact('client', 'nextTaxYearLabel', 'canRollover', 'statusMessage', 'lastMonthName')); 
    }
    
    public function processNewYear(Request $request, TaxClient $client) { 
        $request->validate(['increment_type' => 'required|in:none,percentage,fixed', 'increment_value' => 'nullable|numeric|min:0']);
        
        $lastSheet = $client->salarySheets()->where('status', 'finalized')->orderBy('month', 'desc')->first();
        if (!$lastSheet || Carbon::parse($lastSheet->month)->month != 6) {
            return back()->with('error', 'Cannot rollover. The current tax year must be concluded (Last finalized month must be June).');
        }

        DB::beginTransaction();
        try {
            $employees = $client->employees()->where('status', 'active')->get();
            foreach($employees as $emp) {
                if($request->increment_type == 'percentage' && $request->increment_value > 0) {
                    $emp->current_basic_salary += ($emp->current_basic_salary * ($request->increment_value / 100));
                } elseif ($request->increment_type == 'fixed' && $request->increment_value > 0) {
                    $emp->current_basic_salary += $request->increment_value;
                }
                
                // Reset YTD History
                $emp->opening_gross_salary = 0; 
                $emp->opening_taxable_income = 0; 
                $emp->opening_tax_paid = 0; 
                $emp->current_bonus = 0;
                $emp->save();
            }
            
            $client->update(['saved_salary_months' => null]);

            DB::commit();
            return redirect()->route('tax-services.clients.salary', $client->id)->with('success', 'New Tax Year Started Successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); return back()->with('error', $e->getMessage());
        }
    }
    
    // --- REPORTING ---
    public function getTaxDeductionData(Request $request, TaxClient $client) {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        $items = TaxClientSalaryItem::whereHas('sheet', function($q) use($client, $start, $end){
            $q->where('tax_client_id', $client->id)->where('status', 'finalized')->whereBetween('month', [$start, $end]);
        })->with(['employee', 'sheet'])->get();

        $data = $items->map(function($item, $key) {
            return [
                's_no' => $key + 1,
                'name' => $item->employee->name,
                'designation' => $item->employee->designation ?? '-',
                'gross_pay' => number_format($item->gross_salary),
                'tax_deducted' => number_format($item->income_tax),
                'payment_date' => Carbon::parse($item->sheet->month)->endOfMonth()->format('d-M-Y')
            ];
        });
        return response()->json(['data' => $data]);
    }

    public function exportTaxDeductionReport(Request $request, TaxClient $client)
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();
        
        $fileName = 'Tax_Deduction_Report_' . $start->format('M_Y') . '_to_' . $end->format('M_Y') . '.csv';
        $headers = [
            "Content-type" => "text/csv", 
            "Content-Disposition" => "attachment; filename=$fileName", 
            "Pragma" => "no-cache"
        ];

        $items = TaxClientSalaryItem::whereHas('sheet', function($q) use($client, $start, $end){
            $q->where('tax_client_id', $client->id)->where('status', 'finalized')->whereBetween('month', [$start, $end]);
        })->with(['employee', 'sheet'])->get();

        $callback = function() use($items, $client, $start, $end) {
            $file = fopen('php://output', 'w');
            
            // 1. Top Headers matching sample file
            fputcsv($file, [$client->name]);
            fputcsv($file, ['Tax Deduction Detail']);
            fputcsv($file, ['For the Period ' . $start->format('F, Y') . ' to ' . $end->format('F, Y')]);
            fputcsv($file, []); // Empty Row

            // 2. Column Headers
            fputcsv($file, [
                'Payment Section', 'Employee NTN', 'Employee CNIC', 'Employee Name', 
                'Employee City', 'Employee Address', 'Employee Status', 'Gross Salary', 'Tax Deducted'
            ]);

            // 3. Data Rows
            foreach ($items as $item) {
                fputcsv($file, [
                    '149(1)', 
                    '', // NTN (Blank if not in DB)
                    $item->employee->cnic,
                    $item->employee->name,
                    '', // City
                    '', // Address
                    'Individual',
                    number_format($item->gross_salary, 0, '.', ''),
                    number_format($item->income_tax, 0, '.', '')
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    public function viewTaxDeductionReport(Request $request, TaxClient $client)
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        $items = TaxClientSalaryItem::whereHas('sheet', function($q) use($client, $start, $end){
            $q->where('tax_client_id', $client->id)
              ->where('status', 'finalized')
              ->whereBetween('month', [$start, $end]);
        })->with(['employee', 'sheet'])->get();

        return view('tax-services.reports.tax_deduction_view', compact('client', 'items', 'start', 'end'));
    }

    public function ProjectionReport(Request $request, TaxCalculatorService $taxCalculator)
    {
        $clientId = $request->route('client'); 
        $client = $clientId instanceof TaxClient ? $clientId : TaxClient::findOrFail($clientId);

        $taxYear = $request->input('tax_year', 2025); 
        $taxYearStart = Carbon::createFromDate($taxYear, 7, 1)->startOfDay();
        $taxYearEnd   = Carbon::createFromDate($taxYear + 1, 6, 30)->endOfDay();
        $systemOnboardingDate = Carbon::createFromDate(2025, 11, 1)->startOfMonth(); 
        $currentDate = now()->startOfMonth();

        $reportColumns = [];
        $period = \Carbon\CarbonPeriod::create($taxYearStart, '1 month', $taxYearEnd);
        foreach ($period as $date) {
            $mDate = $date->copy()->startOfMonth();
            if ($mDate->greaterThanOrEqualTo($systemOnboardingDate)) {
                $type = 'past';
                if ($mDate->equalTo($currentDate)) $type = 'current';
                elseif ($mDate->gt($currentDate))  $type = 'future';
                $reportColumns[] = ['date' => $mDate, 'label' => $mDate->format('M-Y'), 'key' => $mDate->format('Y-m'), 'type' => $type];
            }
        }

        $employees = TaxClientEmployee::where('tax_client_id', $client->id)
            ->with(['salaryItems' => function($query) use ($taxYearStart, $taxYearEnd) {
                $query->whereHas('sheet', function($q) use ($taxYearStart, $taxYearEnd) {
                    $q->whereBetween('month', [$taxYearStart, $taxYearEnd]);
                });
            }, 'salaryItems.sheet'])
            ->get();

        $projectionData = [];
        foreach ($employees as $emp) {
            $openingGross = $emp->opening_gross_salary ?? 0;
            $openingTaxable = $emp->opening_taxable_income ?? 0;
            $openingTax = $emp->opening_tax_paid ?? 0;

            $preSystemItems = $emp->salaryItems->filter(function($item) use ($systemOnboardingDate, $taxYearStart) {
                if (!$item->sheet) return false;
                $sDate = Carbon::parse($item->sheet->month);
                return $sDate->lt($systemOnboardingDate) && $sDate->gte($taxYearStart);
            });

            $ytdGross = $openingGross + $preSystemItems->sum('gross_salary');
            $ytdTaxable = $openingTaxable + $preSystemItems->sum('taxable_income_monthly');
            $ytdTax = $openingTax + $preSystemItems->sum('income_tax');

            $baseGross = 0; $baseTaxable = 0;
            $latestSlip = $emp->salaryItems->sortByDesc(fn($i) => $i->sheet->month ?? '')->first();
            if ($latestSlip) {
                $baseGross = $latestSlip->gross_salary;
                $baseTaxable = ($latestSlip->taxable_income_monthly > 0) ? $latestSlip->taxable_income_monthly : $baseGross;
            }

            $monthlyData = [];
            $monthsRemaining = 0;
            $projectedAnnualTaxable = $ytdTaxable; 

            foreach ($reportColumns as $col) {
                $colKey = $col['key'];
                $actualItem = $emp->salaryItems->first(function($item) use ($colKey) {
                    return $item->sheet && Carbon::parse($item->sheet->month)->format('Y-m') === $colKey;
                });

                if ($actualItem) {
                    $mGross = $actualItem->gross_salary;
                    $mTaxable = ($actualItem->taxable_income_monthly > 0) ? $actualItem->taxable_income_monthly : $mGross;
                    $mTax = $actualItem->income_tax;
                    if ($mTax <= 0 && $mGross > 0) $mTax = $taxCalculator->calculateMonthlyTaxFromGross($mGross, $col['date']);
                    $baseGross = $mGross; $baseTaxable = $mTaxable;
                } else {
                    $mGross = $baseGross; $mTaxable = $baseTaxable; $mTax = 0; 
                    $monthsRemaining++;
                }
                $monthlyData[$colKey] = ['gross' => $mGross, 'taxable' => $mTaxable, 'tax' => $mTax, 'is_projected' => !$actualItem, 'type' => $col['type']];
                $projectedAnnualTaxable += $mTaxable;
            }

            $annualTaxLiability = $taxCalculator->calculateTaxFromAnnualIncome($projectedAnnualTaxable, $taxYearEnd);
            $taxPaidSoFar = $ytdTax;
            foreach ($monthlyData as $data) { if (!$data['is_projected']) $taxPaidSoFar += $data['tax']; }
            $remainingTax = max(0, $annualTaxLiability - $taxPaidSoFar);
            $monthlyProjectedTax = ($monthsRemaining > 0) ? ($remainingTax / $monthsRemaining) : 0;

            foreach ($reportColumns as $col) {
                $colKey = $col['key'];
                if ($monthlyData[$colKey]['is_projected']) $monthlyData[$colKey]['tax'] = $monthlyProjectedTax;
            }

            $projectionData[] = ['employee' => $emp, 'ytd' => ['gross' => $ytdGross, 'taxable' => $ytdTaxable, 'tax' => $ytdTax], 'months' => array_values($monthlyData)];
        }

        return view('tax-services.projection_report', compact('client', 'projectionData', 'reportColumns', 'taxYear', 'systemOnboardingDate'));
    }

    // =============================================================================================
    // 8. TAX CERTIFICATES (NEW MODULE)
    // =============================================================================================

    public function tabCertificates(TaxClient $client)
    {
        if ($client->business_id !== Auth::user()->business_id) abort(403);
        $employees = $client->employees()->where('status', 'active')->orderBy('name')->get();
        return view('tax-services.tabs.certificates', compact('client', 'employees'));
    }

    public function printTaxCertificates(Request $request, TaxClient $client)
    {
        $request->validate([
            'tax_year' => 'required|integer', 
            'employee_id' => 'required',
        ]);

        $year = $request->tax_year;
        $start = Carbon::create($year, 7, 1)->startOfDay();       
        $end   = Carbon::create($year + 1, 6, 30)->endOfDay();    

        // 1. Fetch Items
        $query = TaxClientSalaryItem::whereHas('sheet', function($q) use($client, $start, $end){
            $q->where('tax_client_id', $client->id)
              ->where('status', 'finalized')
              ->whereBetween('month', [$start, $end]);
        })->with(['employee', 'sheet']);

        if($request->employee_id !== 'all') {
            $query->where('tax_client_employee_id', $request->employee_id);
        }

        $items = $query->get();

        if($items->isEmpty()) {
            return back()->with('error', 'No finalized salary records found for this period.');
        }

        $grouped = $items->groupBy('tax_client_employee_id');
        $certificates = [];

        foreach($grouped as $empId => $empItems) {
            $employee = $empItems->first()->employee;
            
            // Format Items
            $details = $empItems->sortBy(function($item){ return $item->sheet->month; })->map(function($item){
                return [
                    'month' => Carbon::parse($item->sheet->month)->format('F, Y'),
                    'gross' => $item->gross_salary,
                    'tax' => $item->income_tax
                ];
            });

            // --- YTD / OPENING BALANCE INJECTION ---
            $totalGross = $empItems->sum('gross_salary');
            $totalTax = $empItems->sum('income_tax');
            
            $firstItemDate = Carbon::parse($empItems->sortBy('sheet.month')->first()->sheet->month);
            $taxYearStart = Carbon::create($year, 7, 1);

            // If First item is NOT July, and Opening Balances Exist
            if ($firstItemDate->gt($taxYearStart) && ($employee->opening_gross_salary > 0 || $employee->opening_tax_paid > 0)) {
                $periodEnd = $firstItemDate->copy()->subMonth();
                $label = "July " . $year . " to " . $periodEnd->format('F Y') . " (Opening)";
                
                $details->prepend([
                    'month' => $label,
                    'gross' => $employee->opening_gross_salary,
                    'tax' => $employee->opening_tax_paid
                ]);

                $totalGross += $employee->opening_gross_salary;
                $totalTax += $employee->opening_tax_paid;
            }
            // ----------------------------------------

            $certificates[] = [
                'employee' => $employee,
                'designation' => $employee->designation ?? 'N/A',
                'cnic' => $employee->cnic,
                'items' => $details,
                'total_gross' => $totalGross,
                'total_tax' => $totalTax,
                'period_text' => "July 01, $year to June 30, " . ($year + 1)
            ];
        }

        $fyLabel = "$year - " . ($year + 1);
        $printDate = now()->format('d-M-Y');

        return view('tax-services.reports.certificate_print', compact('client', 'certificates', 'fyLabel', 'printDate'));
    }
}