<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SalaryRevisionController extends Controller
{
    /**
     * Display all salary revisions for an employee
     */
    public function index(Employee $employee)
    {
        $revisions = SalaryStructure::where('employee_id', $employee->id)
            ->orderBy('effective_date')
            ->get();

        return view('employees.revisions.index', compact('employee', 'revisions'));
    }

    /**
     * Show the form for creating a new salary revision
     */
    public function create(Employee $employee)
{
    $this->authorize('update', $employee);

    // ✅ Load related models
    $employee->load([
        'designationRelation:id,name',
        'departmentRelation:id,name',
    ]);

    // ✅ Fetch the latest approved salary structure
    $latestStructure = SalaryStructure::where('employee_id', $employee->id)
        ->where('status', 'approved')
        ->orderByDesc('approved_at')
        ->orderByDesc('id')
        ->first();

    // ✅ Fallback to most recent (any status) if none approved
    if (!$latestStructure) {
        $latestStructure = SalaryStructure::where('employee_id', $employee->id)
            ->orderByDesc('effective_date')
            ->first();
    }

    // ✅ Normalize structure data
    $structureData = $latestStructure ? $latestStructure->toArray() : [];
    $structureData['effective_date'] = now()->toDateString();

    // ✅ Decode salary components properly (no double JSON)
    $rawComponents = $latestStructure?->salary_components ?? [];
    if (is_string($rawComponents)) {
        $decoded = json_decode($rawComponents, true);
        $structureData['salary_components'] = is_array($decoded) ? $decoded : [];
    } else {
        $structureData['salary_components'] = $rawComponents;
    }

    // ✅ Use current salaries and taxes
    $currentBasicSalary = (float)($latestStructure->basic_salary ?? $employee->basic_salary ?? 0);
    $currentMonthlyTax  = 0;

    try {
        if (class_exists(\App\Services\TaxCalculatorService::class)) {
            $taxCalculator = new \App\Services\TaxCalculatorService();
            $currentMonthlyTax = $taxCalculator->calculateMonthlyTax($employee, $currentBasicSalary);
        }
    } catch (\Throwable $e) {
        \Log::warning('Tax calculation skipped: ' . $e->getMessage());
    }

    // ✅ Pass to view
    return view('employees.revisions.create', [
        'employee'           => $employee,
        'structureData'      => $structureData,
        'currentBasicSalary' => $currentBasicSalary,
        'currentMonthlyTax'  => $currentMonthlyTax,
    ]);
}

    /**
     * Store a newly created salary revision (Pending)
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'effective_date'   => 'required|date',
            'new_basic_salary' => 'required|numeric|min:0',
            'components'       => 'required|array',
        ]);

        $components = [];
        foreach ($request->components as $name => $amount) {
            $amount = str_replace(['(', ')', ','], '', $amount);
            $amount = (float) $amount;
            $type = str_contains(strtolower($name), 'fund') ||
                    str_contains(strtolower($name), 'eobi') ||
                    str_contains(strtolower($name), 'tax')
                ? 'deduction' : 'allowance';
            $components[] = [
                'name'   => $name,
                'type'   => $type,
                'amount' => $amount,
            ];
        }

        SalaryStructure::create([
            'employee_id'        => $employee->id,
            'effective_date'     => $request->effective_date,
            'basic_salary'       => $request->new_basic_salary,
            'salary_components'  => json_encode($components),
            'status'             => 'pending',
            'approved_by'        => null,
            'approved_at'        => null,
        ]);

        return redirect()
            ->route('employees.revisions.index', $employee)
            ->with('success', 'Salary revision submitted for approval.');
    }

    /**
     * Show pending revision for approval
     */
    public function showForApproval($id)
    {
        $structure = SalaryStructure::with('employee')->findOrFail($id);
        $employee  = $structure->employee;

        // ✅ Load designation/department safely
        $employee->load([
            'designationRelation' => fn($q) => $q->withoutGlobalScopes()->select('id', 'name'),
            'departmentRelation'  => fn($q) => $q->withoutGlobalScopes()->select('id', 'name'),
        ]);

        $structureData = collect($structure->salary_components ?? []);
        $currentBasicSalary = $structure->basic_salary;
        $currentMonthlyTax = 0;

        try {
            if (class_exists(TaxCalculatorService::class)) {
                $taxCalculator = new TaxCalculatorService();
                $currentMonthlyTax = $taxCalculator->calculateMonthlyTax($employee, $currentBasicSalary);
            }
        } catch (\Throwable $e) {
            Log::warning('Tax calculation skipped: ' . $e->getMessage());
        }

        return view('employees.revisions.approval', [
            'employee'           => $employee,
            'structure'          => $structure,
            'structureData'      => $structureData,
            'currentBasicSalary' => $currentBasicSalary,
            'currentMonthlyTax'  => $currentMonthlyTax,
        ]);
    }

    /**
     * Approve revision
     */
    public function approve(SalaryStructure $structure)
    {
        $employee   = $structure->employee;
        $components = collect($structure->salary_components ?? []);

        DB::transaction(function () use ($employee, $structure, $components) {
            $employee->salaryComponents()->sync([]);
            foreach ($components as $comp) {
                $componentModel = \App\Models\SalaryComponent::where('name', $comp['name'])->first();
                if ($componentModel) {
                    $employee->salaryComponents()->attach($componentModel->id, ['amount' => $comp['amount']]);
                }
            }

            $gross = $structure->basic_salary + $components->where('type', 'allowance')->sum('amount');
            $net   = $gross - $components->where('type', 'deduction')->sum('amount');

            $employee->update([
                'basic_salary' => $structure->basic_salary,
                'gross_salary' => $gross,
                'net_salary'   => $net,
            ]);

            $structure->update([
                'status'       => 'approved',
                'approved_by'  => Auth::id(),
                'approved_at'  => now(),
            ]);
        });

        return redirect()
            ->route('salary.revisions.approve.view', $structure->id)
            ->with('success', 'Salary revision approved successfully.');
    }

    /**
     * Reject revision
     */
    public function reject(SalaryStructure $structure)
    {
        $structure->update([
            'status'       => 'rejected',
            'approved_by'  => Auth::id(),
            'approved_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Salary revision rejected.');
    }

    /**
     * List all pending salary revisions (for admin/HR)
     */
    public function listPending()
    {
        $revisions = SalaryStructure::where('status', 'pending')
            ->with([
                'employee' => fn($q) => $q->select('id', 'name', 'designation_id', 'department_id')
                    ->with([
                        'designationRelation:id,name',
                        'departmentRelation:id,name',
                    ]),
            ])
            ->orderBy('effective_date', 'asc')
            ->get();

        return view('approvals.list', compact('revisions'));
    }

    /**
     * Show individual revision details
     */
    public function show(Employee $employee, SalaryStructure $revision)
    {
        $structureData = is_string($revision->salary_components)
            ? json_decode($revision->salary_components, true)
            : $revision->salary_components;

        return view('employees.revisions.show', [
            'employee'      => $employee,
            'revision'      => $revision,
            'structureData' => $structureData,
        ]);
    }
}
