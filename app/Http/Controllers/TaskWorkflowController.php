<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskWorkflowController extends Controller
{
    // --- 1. EMPLOYEE: START WORK ---
    public function acceptTask(Task $task)
    {
        if(Auth::user()->employee->id !== $task->assigned_to) abort(403);

        $task->update(['status' => 'In Progress', 'start_date' => now()]);
        
        // Auto-start timer if you want, or let them start manually
        $this->startTimerLogic($task); 

        return back()->with('success', 'Task Accepted. Status set to In Progress.');
    }

    public function employeeRejectTask(Request $request, Task $task)
    {
        if(Auth::user()->employee->id !== $task->assigned_to) abort(403);

        $task->update(['status' => 'Rejected']); // Or specific status like 'Returned'
        
        // Add comment
        $task->messages()->create([
            'sender_id' => Auth::id(),
            'message' => 'TASK DECLINED BY EMPLOYEE: ' . $request->reason
        ]);

        return back()->with('warning', 'Task Rejected.');
    }

    // --- 2. EMPLOYEE: DELEGATE ---
    public function reassignTask(Request $request, Task $task)
    {
        if(Auth::user()->employee->id !== $task->assigned_to) abort(403);
        
        $request->validate(['new_employee_id' => 'required|exists:employees,id']);

        $oldEmp = $task->assignedEmployee->name;
        $newEmp = Employee::find($request->new_employee_id);

        $task->update([
            'assigned_to' => $request->new_employee_id,
            'status' => 'Pending' // Reset to pending for new user to accept
        ]);

        $task->messages()->create([
            'sender_id' => Auth::id(),
            'message' => "Re-assigned task from $oldEmp to " . $newEmp->name
        ]);

        return back()->with('success', 'Task Re-assigned successfully.');
    }

    // --- 3. EMPLOYEE: FINISH WORK ---
    public function markExecuted(Task $task)
    {
        // Stop timer if running
        $timer = $task->activeTimer()->first();
        if($timer) {
            $timer->update(['stopped_at' => now(), 'duration_minutes' => $timer->started_at->diffInMinutes(now())]);
        }

        $task->update([
            'status' => 'Executed',
            'executed_at' => now()
        ]);

        return back()->with('success', 'Task Marked as Executed. Waiting for Admin Approval.');
    }

    // --- 4. ADMIN: SUPERVISOR & FINALIZATION ---
    public function addSupervisor(Request $request, Task $task)
    {
        // Only Admin or Creator
        if (!Auth::user()->hasRole(['Admin', 'Owner']) && Auth::id() !== $task->created_by) abort(403);

        $task->update(['supervisor_id' => $request->supervisor_id]);
        return back()->with('success', 'Supervisor Added.');
    }

    public function finalizeTask(Task $task)
    {
        // Admin or Creator Only
        if (!Auth::user()->hasRole(['Admin', 'Owner']) && Auth::id() !== $task->created_by) abort(403);

        $task->update([
            'status' => 'Closed', // Final status
            'completed_at' => now()
        ]);

        return back()->with('success', 'Task Finalized and Closed.');
    }

    public function adminRejectExecution(Request $request, Task $task)
    {
        // Admin/Creator rejects the "Executed" work
        if (!Auth::user()->hasRole(['Admin', 'Owner']) && Auth::id() !== $task->created_by) abort(403);

        $task->update(['status' => 'In Progress']); // Send back to employee

        $task->messages()->create([
            'sender_id' => Auth::id(),
            'message' => 'EXECUTION REJECTED (Redo Required): ' . $request->reason
        ]);

        return back()->with('error', 'Task sent back to In Progress.');
    }

    // --- TIMER HELPERS ---
    public function startTimer(Task $task) { $this->startTimerLogic($task); return back(); }
    public function stopTimer(Task $task) {
        $timer = $task->activeTimer()->first();
        if($timer) $timer->update(['stopped_at' => now(), 'duration_minutes' => $timer->started_at->diffInMinutes(now())]);
        return back();
    }

    private function startTimerLogic($task) {
        if(!$task->activeTimer()->exists()) {
            TaskTimeLog::create(['task_id' => $task->id, 'user_id' => Auth::id(), 'started_at' => now()]);
        }
    }
}