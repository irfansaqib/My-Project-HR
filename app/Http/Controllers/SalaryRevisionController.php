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
    public function index(Employee $employee)
    {
        $revisions = SalaryStructure::where('employee_id', $employee->id)
            ->orderByDesc('effective_date')
            ->get();

        return view('employees.revisions.index', compact('employee', 'revisions'));
    }

    public function create(Employee $employee)
    {
        $this->authorize('update', $employee);

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
            // Handle potential string vs array
            $comps = $latestApproved->salary_components;
            if (is_string($comps)) $comps = json_decode($comps, true);
            $savedComponents = collect($comps ?? [])->keyBy('name');
        } else {
            $currentBasicSalary = $employee->basic_salary ?? 0;
            $employee->load('salaryComponents');
            $savedComponents = $employee->salaryComponents->keyBy('name')->map(function ($c) {
                return ['name' => $c->name, 'type' => $c->type, 'amount' => $c->pivot->amount];
            });
        }
        
        $components = $allComponents->map(function ($component) use ($savedComponents) {
            $savedData = $savedComponents->get($component->name);
            return [
                'id'     => $component->id,
                'name'   => $component->name,
                'type'   => $component->type,
                'amount' => $savedData['amount'] ?? 0,
            ];
        });

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

        // ✅ FIX: No json_encode() needed, let Model cast handle it
        SalaryStructure::create([
            'employee_id'       => $employee->id,
            'effective_date'    => $validated['effective_date'],
            'basic_salary'      => $validated['new_basic_salary'],
            'salary_components' => $componentsJson, // Passed as array
            'status'            => 'pending',
            'created_by'        => Auth::id(),
        ]);

        return redirect()->route('employees.revisions.index', $employee)
            ->with('success', 'Salary revision submitted for approval.');
    }

    public function edit(Employee $employee, $revisionId)
    {
        $this_revision = SalaryStructure::findOrFail($revisionId);
        if ($this_revision->status !== 'pending') {
            return redirect()->route('employees.revisions.index', $employee)->with('error', 'Only pending revisions can be edited.');
        }
        
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
            $comps = $latestApproved->salary_components;
            if (is_string($comps)) $comps = json_decode($comps, true);
            $savedComponents = collect($comps ?? [])->keyBy('name');
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
        
        $revisionBasic = $this_revision->basic_salary;
        
        $revComps = $this_revision->salary_components;
        if (is_string($revComps)) $revComps = json_decode($revComps, true);
        $revisionComponents = collect($revComps ?? [])->keyBy('id');

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

        // ✅ FIX: No json_encode() needed
        $revision->update([
            'effective_date'    => $validated['effective_date'],
            'basic_salary'      => $validated['new_basic_salary'],
            'salary_components' => $componentsJson, // Passed as array
        ]);

        return redirect()->route('employees.revisions.index', $employee)
            ->with('success', 'Salary revision updated successfully.');
    }

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