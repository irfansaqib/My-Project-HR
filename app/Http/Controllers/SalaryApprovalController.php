<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use App\Models\Employee;
use App\Models\SalaryComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryApprovalController extends Controller
{
    /**
     * Show all pending salary revisions for approval.
     */
    public function index()
    {
        $revisions = SalaryStructure::with([
            'employee' => function ($q) {
                // We only need basic employee info here,
                // the 'designation' and 'department' are simple string columns
                $q->select('id', 'name', 'designation', 'department', 'business_id');
            }
        ])
        ->where('status', 'pending')
        ->latest()
        ->get();

        return view('approvals.salary.index', compact('revisions'));
    }

    /**
     * Display detailed review view for a pending salary revision.
     */
    public function show($id)
    {
        $structure = SalaryStructure::with('employee')->findOrFail($id);
        $employee = $structure->employee;
        
        $pendingComponents = collect(is_string($structure->salary_components)
            ? json_decode($structure->salary_components, true)
            : ($structure->salary_components ?? [])
        );

        // Fetch latest approved (previous) structure for comparison
        $previousStructure = SalaryStructure::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('id', '<>', $structure->id)
            ->orderByDesc('effective_date')
            ->first();

        // Fallback to original employee data if no previous revision
        if (!$previousStructure) {
             $employee->load('salaryComponents');
             $previousComponents = $employee->salaryComponents->map(function ($c) {
                return ['name' => $c->name, 'type' => $c->type, 'amount' => $c->pivot->amount, 'id' => $c->id];
             });
             $currentBasic = $employee->basic_salary ?? 0;
        } else {
             $previousComponents = collect(is_string($previousStructure->salary_components)
                ? json_decode($previousStructure->salary_components, true)
                : ($previousStructure->salary_components ?? [])
             );
             $currentBasic = $previousStructure->basic_salary ?? 0;
        }

        // ✅ *** THE FIX ***
        // REMOVED the entire 'if' block that was forcing
        // the 'Income Tax (Estimated)' row.
        
        $allComponentNames = $pendingComponents->pluck('name')->merge($previousComponents->pluck('name'))->unique();
        $allComponents = SalaryComponent::whereIn('name', $allComponentNames)->get()->keyBy('name');
        
        $pendingData = $pendingComponents->keyBy('name');
        $previousData = $previousComponents->keyBy('name');

        $pendingBasic = $structure->basic_salary;

        // Calculate all 4 totals
        $currentAllowances = $previousComponents->where('type', 'allowance')->sum('amount');
        $currentDeductions = $previousComponents->where('type', 'deduction')->sum('amount');
        $currentGross = $currentBasic + $currentAllowances;
        $currentNet = $currentGross - $currentDeductions;
        
        $pendingAllowances = $pendingComponents->where('type', 'allowance')->sum('amount');
        $pendingDeductions = $pendingComponents->where('type', 'deduction')->sum('amount');
        $pendingGross = $pendingBasic + $pendingAllowances;
        $pendingNet = $pendingGross - $pendingDeductions;

        // Pass all 4 totals (and all other data) to the view
        return view('approvals.salary.show', compact(
            'employee', 
            'structure', 
            'allComponents', 
            'pendingData', 
            'previousData', 
            'currentBasic', 
            'pendingBasic',
            'pendingComponents',
            'previousComponents',
            'currentGross',
            'currentNet',
            'pendingGross',
            'pendingNet'
        ));
    }


    /**
     * Approve and apply salary revision.
     */
    public function approve(SalaryStructure $structure)
    {
        $employee = $structure->employee;
        $components = collect(
            is_string($structure->salary_components)
                ? json_decode($structure->salary_components, true)
                : $structure->salary_components
        );

        DB::transaction(function () use ($employee, $structure, $components) {
            
            // 1. PREPARE SYNC DATA FOR PIVOT TABLE
            $syncData = [];
            foreach ($components as $comp) {
                if (isset($comp['id'])) {
                    $syncData[$comp['id']] = ['amount' => $comp['amount']];
                }
            }
            
            // 2. SYNC WITH THE PIVOT TABLE
            $employee->salaryComponents()->sync($syncData);

            // 3. Recalculate totals
            $gross = $structure->basic_salary + $components->where('type', 'allowance')->sum('amount');
            $deductions = $components->where('type', 'deduction')->sum('amount');
            $net = $gross - $deductions; 

            // 4. Update Employee's main record
            $employee->update([
                'basic_salary' => $structure->basic_salary,
                'gross_salary' => $gross,
                'net_salary'   => $net,
            ]);

            // 5. Mark structure as approved
            $structure->update([
                'status'       => 'approved',
                'approved_by'  => Auth::id(),
                'approved_at'  => now(),
            ]);
        });

        return redirect()->route('approvals.salary.index')
            ->with('success', 'Salary revision approved successfully.');
    }

    /**
     * Reject salary revision.
     */
    public function reject(SalaryStructure $structure)
    {
        $structure->update([
            'status'       => 'rejected',
            'approved_by'  => Auth::id(),
            'approved_at'  => now(),
        ]);

        return redirect()->route('approvals.salary.index')
            ->with('success', 'Salary revision rejected.');
    }
}