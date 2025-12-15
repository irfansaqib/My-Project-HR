<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TaskCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // Added for email logic if you uncomment it later

class ClientPortalController extends Controller
{
    /**
     * Client Dashboard
     * Matches variables required by dashboard.blade.php
     */
    public function dashboard()
    {
        $user = Auth::user();
        $client = $user->client; // Assumes User -> hasOne -> Client
        
        // --- FIX: HANDLE MISSING CLIENT PROFILE WITHOUT REDIRECT LOOP ---
        if (!$client) {
            // If no client profile exists, we simply return the view with zero values.
            // You can add an alert in your blade file: @if(!$client) <div class="alert">...</div> @endif
            return view('client_portal.dashboard', [
                'client' => null,
                'activeTasks' => 0,
                'pendingTasks' => 0,
                'recentTasks' => collect([]), // Empty collection
                'error' => 'Client profile not linked. Please contact support.'
            ]);
        }

        // 1. Active Tasks (Open or In Progress)
        $activeTasks = $client->tasks()
                        ->whereIn('status', ['Open', 'In Progress'])
                        ->count();

        // 2. Pending Tasks (Waiting for Approval)
        $pendingTasks = $client->tasks()
                        ->where('status', 'Pending')
                        ->count();

        // 3. Recent Tasks
        $recentTasks = $client->tasks()
                        ->with('category')
                        ->latest()
                        ->take(5)
                        ->get();

        return view('client_portal.dashboard', compact('client', 'activeTasks', 'pendingTasks', 'recentTasks'));
    }
    /**
     * List all Tasks
     */
    public function indexTasks()
    {
        $client = Auth::user()->client;
        if (!$client) abort(403, 'Client profile not found');

        $tasks = $client->tasks()
                    ->with(['category', 'assignedEmployee'])
                    ->latest()
                    ->paginate(10);
                    
        return view('client_portal.tasks.index', compact('tasks'));
    }

    /**
     * Show Create Task Form
     */
    public function createTask()
    {
        // Fetch Level 0 Categories with nested children
        $categories = TaskCategory::where('level', 0)->with('children.children')->get();
        return view('client_portal.tasks.create', compact('categories'));
    }
    
    /**
     * Store New Task
     */
    public function storeTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Normal,High,Urgent',
            'due_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:10240',
        ]);

        // Handle File Upload
        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('task_attachments', 'public');
        }

        // Get the Client Profile associated with the logged-in User
        // Assuming the Logged in User has a 'client' relationship or is the client
        $user = Auth::user();
        $clientProfile = $user->client; // Ensure your User model has this relationship

        // AUTOMATION LOGIC: Check for Default Employee
        $assignedTo = null;
        $status = 'Pending'; // Default status
        
        if ($clientProfile && $clientProfile->defaultEmployee) {
            $assignedTo = $clientProfile->defaultEmployee->id;
            $status = 'In Progress'; // Automatically mark as In Progress since it's assigned
        }

        // Create the Task
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => $status, 
            'client_id' => $clientProfile->id ?? null,
            'created_by' => $user->id,
            'assigned_to' => $assignedTo, // <--- HERE IS THE AUTOMATION
            'attachment_path' => $path,
        ]);

        // NOTIFICATION LOGIC
        if ($assignedTo) {
            // Notify the specific employee immediately
            $employeeUser = \App\Models\User::where('employee_id', $assignedTo)->first();
            if ($employeeUser) {
                $employeeUser->notify(new \App\Notifications\TaskAlert($task, 'assigned', "New Client Task Auto-Assigned: " . $task->title));
            }
        } else {
            // Fallback: Notify Admins if no one was auto-assigned
            // (Your existing admin notification logic goes here)
        }

        return redirect()->route('client.tasks.index')->with('success', 'Request submitted successfully.');
    }

    /**
     * Show Task Details
     */
    public function showTask(Task $task)
    {
        // Security Check: Ensure task belongs to logged-in client
        if ($task->client_id !== Auth::user()->client->id) {
            abort(403);
        }

        $task->load(['messages.sender', 'assignedEmployee', 'category']);
        return view('client_portal.tasks.show', compact('task'));
    }

    /**
     * Show Edit Form
     */
    public function edit(Task $task)
    {
        if($task->client_id !== Auth::user()->client->id) abort(403);

        $categories = TaskCategory::where('level', 0)->with('children.children')->get();
        
        // Resolve Category IDs for pre-selecting dropdowns
        $selectedLvl2 = $task->task_category_id;
        $selectedLvl1 = $task->category->parent_id ?? null;
        $selectedLvl0 = $task->category->parent->parent_id ?? null;

        return view('client_portal.tasks.edit', compact('task', 'categories', 'selectedLvl0', 'selectedLvl1', 'selectedLvl2'));
    }

    /**
     * Update Task
     */
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

        return redirect()->route('client.tasks.index')->with('success', 'Task updated successfully.');
    }
}