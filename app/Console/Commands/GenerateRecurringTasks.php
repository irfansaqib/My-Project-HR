<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTask;
use App\Models\Task;
use Carbon\Carbon;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'tasks:generate-recurring';
    protected $description = 'Check recurring profiles and generate tasks if due';

    public function handle()
    {
        $profiles = RecurringTask::where('status', 'Active')->get();
        $today = Carbon::today();
        $count = 0;

        foreach ($profiles as $p) {
            // Prevent double run
            if ($p->last_run_at && $p->last_run_at->format('Y-m-d') == $today->format('Y-m-d')) continue;

            $shouldRun = false;
            $taskStart = null;
            $taskEnd = null;

            // --- LOGIC ENGINE ---
            switch ($p->frequency) {
                case 'Daily':
                    $shouldRun = true;
                    $taskStart = Carbon::parse($today->format('Y-m-d') . ' ' . $p->start_time);
                    $taskEnd = Carbon::parse($today->format('Y-m-d') . ' ' . $p->end_time);
                    break;

                case 'Weekly':
                    if ($today->format('l') == $p->day_of_week) {
                        $shouldRun = true;
                        $taskStart = $today->copy(); // Today 00:00
                        $taskEnd = $today->copy()->addDays($p->duration_days);
                    }
                    break;

                case 'Fortnightly':
                    // Check diff in weeks from reference date
                    $diffInWeeks = $p->reference_start_date->diffInWeeks($today);
                    // Run if it's the exact day AND weeks diff is even (0, 2, 4...)
                    if ($today->format('l') == $p->reference_start_date->format('l') && $diffInWeeks % 2 == 0) {
                        $shouldRun = true;
                        $taskStart = $today->copy();
                        $taskEnd = $today->copy()->addDays($p->duration_days);
                    }
                    break;

                case 'Monthly':
                    if ($today->day == $p->month_start_day) {
                        $shouldRun = true;
                        $taskStart = $today->copy();
                        // Due date is same month, specific day
                        $taskEnd = $today->copy()->setDay($p->month_end_day);
                    }
                    break;

                case 'Quarterly':
                    // Check if today matches reference day/month pattern in current quarter
                    // Simplified: We assume reference_start_date is like "1st Jan". 
                    // We run if today is 1st Jan, 1st Apr, 1st Jul, 1st Oct.
                    $refMonth = $p->reference_start_date->month;
                    $refDay = $p->reference_start_date->day;
                    
                    // Logic: If (CurrentMonth - RefMonth) % 3 == 0 AND TodayDay == RefDay
                    if (($today->month - $refMonth) % 3 == 0 && $today->day == $refDay) {
                        $shouldRun = true;
                        $taskStart = $today->copy();
                        // End date is calculated by adding difference between RefStart and RefEnd
                        // Or simplistic: Add 15 days as per requirement
                        $daysGap = $p->reference_start_date->diffInDays($p->annual_end_date ?? $today); 
                        $taskEnd = $today->copy()->addDays($daysGap);
                    }
                    break;

                case 'Annually':
                    if ($today->month == $p->annual_start_date->month && $today->day == $p->annual_start_date->day) {
                        $shouldRun = true;
                        $taskStart = $today->copy();
                        // End date: Same year, but the stored end Month/Day
                        $taskEnd = Carbon::create($today->year, $p->annual_end_date->month, $p->annual_end_date->day);
                    }
                    break;
            }

            // --- GENERATE TASK ---
            if ($shouldRun) {
                // Generate ID
                $lastTask = Task::latest('id')->first();
                $nextId = $lastTask ? $lastTask->id + 1 : 1;
                $taskNumber = 'TSK-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

                Task::create([
                    'task_number' => $taskNumber,
                    'client_id' => $p->client_id,
                    'task_category_id' => $p->task_category_id,
                    'assigned_to' => $p->assigned_to,
                    'created_by' => $p->created_by,
                    'description' => $p->description . ' (Auto-Generated)',
                    'priority' => $p->priority,
                    'start_date' => $taskStart,
                    'due_date' => $taskEnd,
                    'status' => 'Pending'
                ]);

                $p->update(['last_run_at' => $today]);
                $count++;
            }
        }

        $this->info("Generated $count recurring tasks.");
    }
}