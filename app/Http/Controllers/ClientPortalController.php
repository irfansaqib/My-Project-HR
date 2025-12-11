<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TaskCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClientPortalController extends Controller
{
    public function dashboard()
    {
        $client = Auth::user()->client; // Assuming User hasOne Client relationship
        if (!$client) return redirect()->route('client.login')->with('error', 'No Client Profile Linked.');

        $activeTasks = $client->tasks()->whereIn('status', ['In Progress', 'Pending'])->count();
        $completedTasks = $client->tasks()->where('status', 'Completed')->count();
        $recentTasks = $client->tasks()->latest()->take(5)->get();

        return view('client_portal.dashboard', compact('client', 'activeTasks', 'completedTasks', 'recentTasks'));
    }

    public function indexTasks()
    {
        $client = Auth::user()->client;
        $tasks = $client->tasks()->with(['category', 'assignedEmployee'])->latest()->paginate(10);
        return view('client_portal.tasks.index', compact('tasks'));
    }

    public function createTask()
    {
        // Fetch Level 0 Categories (Taxation, Accounting, etc.)
        // We load children recursively for the JS to handle, or we can use AJAX. 
        // For simplicity and speed, passing structured JSON is often easier for 3-level selects.
        $categories = TaskCategory::where('level', 0)->with('children.children')->get();
        return view('client_portal.tasks.create', compact('categories'));
    }
    
    public function edit(Task $task)
    {
        // Security: Ensure task belongs to client
        if($task->client_id !== Auth::user()->client->id) abort(403);

        $categories = TaskCategory::where('level', 0)->with('children.children')->get();
        
        // Resolve Category IDs for Dropdowns
        $selectedLvl2 = $task->task_category_id; // The saved ID
        $selectedLvl1 = $task->category->parent_id ?? null;
        $selectedLvl0 = $task->category->parent->parent_id ?? null;

        return view('client_portal.tasks.edit', compact('task', 'categories', 'selectedLvl0', 'selectedLvl1', 'selectedLvl2'));
    }

    public function update(Request $request, Task $task)
    {
        if($task->client_id !== Auth::user()->client->id) abort(403);

        $request->validate([
            'category_id' => 'required|exists:task_categories,id',
            'description' => 'required|string|max:1000',
            'priority'    => 'required|in:Normal,Urgent,Very Urgent',
            'due_date'    => 'nullable|date',
        ]);

        $task->update([
            'task_category_id' => $request->category_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);

        // Attachment logic would go here

        return redirect()->route('client.tasks.index')->with('success', 'Task updated successfully.');
    }

    public function showTask(Task $task)
    {
        // Security Check
        if ($task->client_id !== Auth::user()->client->id) {
            abort(403);
        }

        $task->load(['messages.sender', 'assignedEmployee', 'category']);
        return view('client_portal.tasks.show', compact('task'));
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:task_categories,id', // This should be the Level 2 ID
            'description' => 'required|string|max:1000',
            'priority'    => 'required|in:Normal,Urgent,Very Urgent',
            'due_date'    => 'nullable|date|after:today',
            'attachments.*' => 'nullable|file|max:5120', // 5MB max
        ]);

        $client = Auth::user()->client;

        DB::beginTransaction();
        try {
            // Auto-Generate Task Number (e.g., TSK-2025-0001)
            $lastTask = Task::latest('id')->first();
            $nextId = $lastTask ? $lastTask->id + 1 : 1;
            $taskNumber = 'TSK-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $task = Task::create([
                'task_number' => $taskNumber,
                'client_id' => $client->id,
                'task_category_id' => $request->category_id,
                'created_by' => Auth::id(),
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 'Pending',
                'due_date' => $request->due_date,
            ]);

            // Handle Attachments (Placeholder logic - assuming you have a Media or Attachment model later)
            // if($request->hasFile('attachments')) { ... }

            DB::commit();
            return redirect()->route('client.tasks.index')->with('success', 'Task Submitted Successfully. Reference: ' . $taskNumber);
            
            / 1. Find Admins (Or a specific email)
            $admins = User::role(['Admin', 'Owner'])->get(); 

            // 2. Send Email
            foreach($admins as $admin) {
                if($admin->email) {
                    Mail::to($admin->email)->send(new TaskNotification($task, 'created'));
                }
            }

            // 3. Optional: Send copy to Client
            if(Auth::user()->email) {
                Mail::to(Auth::user()->email)->send(new TaskNotification($task, 'created', 'Your request has been received.'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating task: ' . $e->getMessage())->withInput();
        }
    }
}