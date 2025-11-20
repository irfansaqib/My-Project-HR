<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Services\TaxCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryRevisionController extends Controller
{
    /**
     * Display a list of salary revisions for a specific employee
     */
    public function index(Employee $employee)
    {
        // ✅ No 'load' needed for designation/department here
        $revisions = SalaryStructure::where('employee_id', $employee->id)
            ->orderByDesc('effective_date')
            ->get();

        return view('employees.revisions.index', compact('employee', 'revisions'));
    }

    /**
     * Show the create form
     */
    public function create(Employee $employee)
    {
        $this->authorize('update', $employee);

        // ✅ No 'load' needed for designation/department here
        
        // 1. Get ALL business salary components
        $allComponents = SalaryComponent::where('business_id', Auth::user()->business_id)
                            ->orderBy('type')->orderBy('name')->get();

        // 2. Get the latest approved salary data
        $latestApproved = $employee->salaryStructures()
                            ->where('status', 'approved')
                            ->latest('effective_date')
                            ->first();

        $currentBasicSalary = 0;
        $savedComponents = collect();

        if ($latestApproved) {
            // Path A: A revision exists. Use it.
            $currentBasicSalary = $latestApproved->basic_salary;
            $savedComponents = collect(json_decode($latestApproved->salary_components ?? '[]', true))->keyBy('name');
        
        } else {
            // Path B: No revision. Use original employee data.
            $currentBasicSalary = $employee->basic_salary ?? 0;
            $employee->load('salaryComponents');
            $savedComponents = $employee->salaryComponents->keyBy('name')->map(function ($c) {
                return ['name' => $c->name, 'type' => $c->type, 'amount' => $c->pivot->amount];
            });
        }
        
        // 3. Merge them
        $components = $allComponents->map(function ($component) use ($savedComponents) {
            $savedData = $savedComponents->get($component->name);
            return [
                'id'     => $component->id,
                'name'   => $component->name,
                'type'   => $component->type,
                'amount' => $savedData['amount'] ?? 0,
            ];
        });

        // 4. Calculate current totals
        $currentAllowancesTotal = $components->where('type', 'allowance')->sum('amount');
        $currentGross = $currentBasicSalary + $currentAllowancesTotal;
        $currentDeductionsTotal = $components->where('type', 'deduction')->sum('amount');
        $currentNet = $currentGross - $currentDeductionsTotal;

        return view('employees.revisions.create', [
            'employee'           => $employee,
            'components'         => $components,
            'currentBasicSalary' => $currentBasicSalary,
            'currentGross'       => $currentGross,
            'currentNet'         => $currentNet,
        ]);
    }

    /**
     * Store new revision (pending)
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'effective_date'   => 'required|date',
            'new_basic_salary' => 'required|numeric|min:0',
            'components'       => 'required|array',
            'components.*'     => 'required|numeric|min:0',
        ]);

        $componentModels = SalaryComponent::whereIn('id', array_keys($validated['components']))
                            ->get()->keyBy('id');

        $componentsJson = [];
        foreach ($validated['components'] as $id => $amount) {
            if ($componentModel = $componentModels->get($id)) {
                $componentsJson[] = [
                    'id'     => $componentModel->id,
                    'name'   => $componentModel->name,
                    'type'   => $componentModel->type,
                    'amount' => (float)$amount,
                ];
            }
        }

        SalaryStructure::create([
            'employee_id'       => $employee->id,
            'effective_date'    => $validated['effective_date'],
            'basic_salary'      => $validated['new_basic_salary'],
            'salary_components' => json_encode($componentsJson),
            'status'            => 'pending',
            'created_by'        => Auth::id(),
        ]);

        return redirect()->route('employees.revisions.index', $employee)
            ->with('success', 'Salary revision submitted for approval.');
    }

    /**
     * Show edit form
     */
    public function edit(Employee $employee, $revisionId, TaxCalculatorService $taxService)
    {
        $this_revision = SalaryStructure::findOrFail($revisionId);
        if ($this_revision->status !== 'pending') {
            return redirect()->route('employees.revisions.index', $employee)->with('error', 'Only pending revisions can be edited.');
        }
        
        // ✅ No 'load' needed for designation/department here
        
        // --- 1. Get "Current" Data (Latest Approved or Fallback) ---
        $allComponents = SalaryComponent::where('business_id', Auth::user()->business_id)
                            ->orderBy('type')->orderBy('name')->get();
        
        $latestApproved = $employee->salaryStructures()
                            ->where('status', 'approved')
                            ->latest('effective_date')
                            ->first();

        $currentBasicSalary = 0;
        $savedComponents = collect();

        if ($latestApproved) {
            $currentBasicSalary = $latestApproved->basic_salary;
            $savedComponents = collect(json_decode($latestApproved->salary_components ?? '[]', true))->keyBy('name');
        } else {
            $currentBasicSalary = $employee->basic_salary ?? 0;
            $employee->load('salaryComponents');
            $savedComponents = $employee->salaryComponents->keyBy('name')->map(function ($c) {
                return ['name' => $c->name, 'type' => $c->type, 'amount' => $c->pivot->amount];
            });
        }
        
        $currentComponents = $allComponents->map(function ($component) use ($savedComponents) {
            $savedData = $savedComponents->get($component->name);
            return [
                'id'     => $component->id,
                'name'   => $component->name,
                'type'   => $component->type,
                'amount' => $savedData['amount'] ?? 0,
            ];
        });
        
        $currentAllowancesTotal = $currentComponents->where('type', 'allowance')->sum('amount');
        $currentGross = $currentBasicSalary + $currentAllowancesTotal;
        $currentDeductionsTotal = $currentComponents->where('type', 'deduction')->sum('amount');
        $currentNet = $currentGross - $currentDeductionsTotal;
        
        // --- 2. Get "Updated" Data (The Pending Revision) ---
        $revisionBasic = $this_revision->basic_salary;
        $revisionComponents = collect(json_decode($this_revision->salary_components ?? '[]', true))->keyBy('id');


        return view('employees.revisions.edit', [
            'employee'           => $employee,
            'revision'           => $this_revision,
            'components'         => $currentComponents,
            'revisionComponents' => $revisionComponents,
            'currentBasicSalary' => $currentBasicSalary,
            'revisionBasic'      => $revisionBasic,
            'currentGross'       => $currentGross,
            'currentNet'         => $currentNet,
        ]);
    }


    /**
     * Update an existing revision (if pending)
     */
    public function update(Request $request, Employee $employee, SalaryStructure $revision)
    {
        $validated = $request->validate([
            'effective_date'   => 'required|date',
            'new_basic_salary' => 'required|numeric|min:0',
            'components'       => 'required|array',
            'components.*'     => 'required|numeric|min:0',
        ]);

        if ($revision->status !== 'pending') {
            return redirect()->route('employees.revisions.index', $employee)->with('error', 'Only pending revisions can be edited.');
        }

        $componentModels = SalaryComponent::whereIn('id', array_keys($validated['components']))
                            ->get()->keyBy('id');

        $componentsJson = [];
        foreach ($validated['components'] as $id => $amount) {
            if ($componentModel = $componentModels->get($id)) {
                $componentsJson[] = [
                    'id'     => $componentModel->id,
                    'name'   => $componentModel->name,
                    'type'   => $componentModel->type,
                    'amount' => (float)$amount,
                ];
            }
        }

        $revision->update([
            'effective_date'    => $validated['effective_date'],
            'basic_salary'      => $validated['new_basic_salary'],
            'salary_components' => json_encode($componentsJson),
        ]);

        return redirect()->route('employees.revisions.index', $employee)
            ->with('success', 'Salary revision updated successfully.');
    }

    /**
     * Delete a pending revision
     */
    public function destroy(Employee $employee, SalaryStructure $revision)
    {
        if ($revision->status !== 'approved') {
            $revision->delete();
            return redirect()->route('employees.revisions.index', $employee)
                ->with('success', 'Salary revision deleted successfully.');
        }

        return redirect()->route('employees.revisions.index', $employee)
            ->with('error', 'Approved revisions cannot be deleted.');
    }
}