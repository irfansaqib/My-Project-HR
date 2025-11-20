<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\SalaryStructure;

class BackfillSalaryStructures extends Command
{
    protected $signature = 'salary:backfill';
    protected $description = 'Populate salary_structures table with current employee salary details as approved snapshots';

    public function handle()
    {
        $employees = Employee::with(['salaryComponents'])->get();

        if ($employees->isEmpty()) {
            $this->warn('⚠️ No employees found.');
            return;
        }

        foreach ($employees as $employee) {
            // Skip if already backfilled
            if (SalaryStructure::where('employee_id', $employee->id)->exists()) {
                $this->line("⏭️  Skipped: {$employee->name} (already backfilled)");
                continue;
            }

            $components = $employee->salaryComponents->map(function ($component) {
                return [
                    'name'   => $component->name,
                    'type'   => $component->type,
                    'amount' => (float) $component->pivot->amount,
                ];
            });

            SalaryStructure::create([
                'employee_id'       => $employee->id,
                'effective_date'    => now()->toDateString(),
                'basic_salary'      => $employee->basic_salary ?? 0,
                'salary_components' => $components->toJson(),
                'status'            => 'approved',
                'approved_by'       => \App\Models\User::query()->value('id'), // first existing user

                'approved_at'       => now(),
            ]);

            $this->info("✅ Backfilled: {$employee->name}");
        }

        $this->info('🎉 Salary structures backfilled successfully.');
    }
}
