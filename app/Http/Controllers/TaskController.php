<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Client;
use App\Models\Employee;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function __construct()
    {
        // 1. View & List Access
        $this->middleware('permission:task-list')->only(['index', 'show', 'myTasks']);
        
        // 2. Creation Access
        $this->middleware('permission:task-create')->only(['create', 'store']);
        
        // 3. Edit/Update Access
        $this->middleware('permission:task-edit')->only(['edit', 'update', 'extendDueDate']);
        
        // 4. Delete Access
        $this->middleware('permission:task-delete')->only(['destroy']);
        
        // 5. Special Reporting Access
        $this->middleware('permission:task-report')->only(['report']);
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Query Logic: Admin sees all, Employee sees assigned + created by them
        $query = Task::with(['client', 'category.parent.parent', 'assignedEmployee', 'creator']);

        if (!$user->hasRole(['Admin', 'Owner'])) {
            $employeeId = $user->employee->id ?? 0;
            $query->where(function($q) use ($employeeId, $user) {
                $q->where('assigned_to', $employeeId)
                  ->orWhere('created_by', $user->id);
            });
        }

        // Search Filter
        if ($request->has('search') && $request->search != '') {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('task_number', 'like', "%$s%")
                  ->orWhere('description', 'like', "%$s%")
                  ->orWhereHas('client', fn($c) => $c->where('business_name', 'like', "%$s%"));
            });
        }

        $tasks = $query->latest()->paginate(15);

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $clients = Client::where('status', 'active')->orderBy('business_name')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $categories = TaskCategory::where('level', 0)->with('children.children')->get(); // Load Tree

        return view('tasks.create', compact('clients', 'employees', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'category_id' => 'required|exists:task_categories,id', // Level 2 ID
            'assigned_to' => 'required',
            'priority' => 'required',
            'description' => 'required',
            'start_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create Task with a Temporary Unique Placeholder
            // We use uniqid() to ensure the temporary string is unique during the transaction
            $task = Task::create([
                'task_number' => 'TEMP-' . uniqid(), 
                'client_id' => $request->client_id,
                'task_category_id' => $request->category_id,
                'assigned_to' => $request->assigned_to,
                'created_by' => Auth::id(),
                'description' => $request->description,
                'priority' => $request->priority,
                'start_date' => $request->start_date,
                'due_date' => $request->due_date,
                'status' => 'Pending'
            ]);

            // 2. Generate Number based on the ACTUAL Database ID
            // Format: TSK-MM-YYYY-0000 (e.g., TSK-12-2025-0045)
            $taskNumber = 'TSK-' . date('m') . '-' . date('Y') . '-' . str_pad($task->id, 4, '0', STR_PAD_LEFT);

            // 3. Update the Task with the correct Number
            $task->update([
                'task_number' => $taskNumber
            ]);

            DB::commit();
            return redirect()->route('tasks.index')->with('success', 'Task Created Successfully: ' . $taskNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(Task $task)
    {
        // Authorization Check
        if (!Auth::user()->hasRole(['Admin', 'Owner']) && Auth::id() !== $task->created_by) {
            return abort(403, 'Unauthorized. Only Admin or Creator can edit.');
        }

        $clients = Client::where('status', 'active')->get();
        $employees = Employee::where('status', 'active')->get();
        $categories = TaskCategory::where('level', 0)->with('children.children')->get();

        // Tree Resolution for Dropdowns
        $selectedLvl2 = $task->task_category_id;
        $selectedLvl1 = $task->category->parent_id ?? null;
        $selectedLvl0 = $task->category->parent->parent_id ?? null;

        return view('tasks.edit', compact('task', 'clients', 'employees', 'categories', 'selectedLvl0', 'selectedLvl1', 'selectedLvl2'));
    }

    public function update(Request $request, Task $task)
    {
        if (!Auth::user()->hasRole(['Admin', 'Owner']) && Auth::id() !== $task->created_by) {
            return abort(403);
        }

        $request->validate([
            'category_id' => 'required|exists:task_categories,id',
            'description' => 'required'
        ]);

        $task->update([
            'client_id' => $request->client_id,
            'task_category_id' => $request->category_id,
            'assigned_to' => $request->assigned_to,
            'priority' => $request->priority,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'status' => $request->status,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task Updated Successfully.');
    }

    public function show(Task $task)
    {
        // Security Check
        if (!Auth::user()->hasRole(['Admin', 'Owner']) && 
            Auth::id() !== $task->created_by && 
            Auth::user()->employee->id !== $task->assigned_to) {
            abort(403);
        }

        $task->load(['messages.sender', 'client', 'assignedEmployee', 'category']);
        return view('tasks.show', compact('task'));
    }

    public function extendDueDate(Request $request, Task $task)
    {
        // 1. Validation
        $request->validate([
            'new_due_date' => 'required|date|after:today',
            'reason' => 'required|string|max:255'
        ]);

        // 2. Authorization (Only Admin or Creator can extend)
        if (!auth()->user()->hasRole(['Admin', 'Owner']) && auth()->id() !== $task->created_by) {
            abort(403, 'Unauthorized action.');
        }

        // 3. Record History
        \App\Models\TaskExtension::create([
            'task_id' => $task->id,
            'changed_by' => auth()->id(),
            'old_due_date' => $task->due_date,
            'new_due_date' => $request->new_due_date,
            'reason' => $request->reason
        ]);

        // 4. Update Task
        $task->update([
            'due_date' => $request->new_due_date
        ]);

        // 5. Add a system message to chat
        $task->messages()->create([
            'sender_id' => auth()->id(),
            'message' => "📅 DUE DATE EXTENDED: From " . 
                         \Carbon\Carbon::parse($task->getOriginal('due_date'))->format('d-M') . 
                         " to " . \Carbon\Carbon::parse($request->new_due_date)->format('d-M') . 
                         ". Reason: " . $request->reason
        ]);

        return back()->with('success', 'Due Date Extended Successfully.');
    }
    
    public function myTasks()
    {
        $employeeId = Auth::user()->employee->id ?? 0;

        // 1. Assigned Today (Created or Start Date is Today)
        $assignedToday = Task::where('assigned_to', $employeeId)
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->with(['client', 'category', 'creator'])
            ->get();

        // 2. Due Today (Due Date is Today AND Not Completed)
        $dueToday = Task::where('assigned_to', $employeeId)
            ->whereDate('due_date', \Carbon\Carbon::today())
            ->whereNotIn('status', ['Completed', 'Closed'])
            ->with(['client', 'category', 'creator'])
            ->get();

        // 3. All My Active Tasks (For reference below)
        $allMyTasks = Task::where('assigned_to', $employeeId)
            ->orderBy('due_date', 'asc')
            ->with(['client', 'category', 'creator'])
            ->paginate(15);

        return view('tasks.my_tasks', compact('assignedToday', 'dueToday', 'allMyTasks'));
    }

    public function destroy(Task $task)
    {
        if (!Auth::user()->hasRole(['Admin', 'Owner']) && Auth::id() !== $task->created_by) {
            return abort(403);
        }
        $task->delete();
        return back()->with('success', 'Task Deleted.');
    }

    public function report(Request $request)
    {
        $query = \App\Models\Task::query();

        // 1. Employee Filter
        if($request->employee_id) {
            $query->where('assigned_to', $request->employee_id);
        }

        // 2. Client Filter
        if($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // 3. Assigned By Filter
        if($request->assigned_by) {
            $query->where('created_by', $request->assigned_by);
        }

        // 4. Status Filter
        if($request->status && $request->status != 'All') {
            if ($request->status == 'Completed') {
                $query->whereIn('status', ['Completed', 'Closed']);
            } elseif ($request->status == 'Overdue') {
                $query->whereDate('due_date', '<', now())->whereNotIn('status', ['Completed', 'Closed']);
            } else {
                $query->where('status', $request->status);
            }
        }

        // 5. Date Filters
        if($request->assigned_date) {
            $query->whereDate('created_at', $request->assigned_date);
        }
        if($request->due_date) {
            $query->whereDate('due_date', $request->due_date);
        }

        $tasks = $query->with(['client', 'assignedEmployee', 'category', 'creator'])->latest()->paginate(20);

        // Stats Logic
        $stats = [
            'total' => $query->count(),
            'completed' => (clone $query)->whereIn('status', ['Completed', 'Closed'])->count(),
            'in_progress' => (clone $query)->where('status', 'In Progress')->count(),
            'overdue' => (clone $query)->whereDate('due_date', '<', now())->whereNotIn('status', ['Completed', 'Closed'])->count(),
            'avg_time' => 0 
        ];

        // Dropdowns
        $employees = \App\Models\Employee::where('status', 'active')->orderBy('name')->get();
        $clients = \App\Models\Client::where('status', 'active')->orderBy('business_name')->get();
        $assigners = \App\Models\User::whereHas('createdTasks')->distinct()->get();

        return view('tasks.report', compact('tasks', 'stats', 'employees', 'clients', 'assigners'));
    }

    public function clientRequests()
    {
        $tasks = \App\Models\Task::whereHas('creator', function($query) {
                $query->role('Client'); 
            })
            ->where('status', '!=', 'Completed') 
            ->with(['client', 'assignedEmployee', 'creator'])
            ->latest()
            ->get();

        return view('tasks.client_requests', compact('tasks'));
    }
}