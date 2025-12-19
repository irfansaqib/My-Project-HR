<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TaskCategory;
use App\Models\Task;
use App\Models\User;
use App\Models\ClientDocument; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClientPortalController extends Controller
{
    /**
     * Client Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $client = $user->client; 
        
        if (!$client) {
            return view('client_portal.dashboard', [
                'client' => null,
                'activeTasks' => 0,
                'pendingTasks' => 0,
                'recentTasks' => collect([]),
                'error' => 'Client profile not linked. Please contact support.'
            ]);
        }

        // 1. Active Tasks
        $activeTasks = $client->tasks()
                        ->whereIn('status', ['Open', 'In Progress'])
                        ->count();

        // 2. Pending Tasks
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
        $categories = TaskCategory::where('level', 0)->with('children.children')->get();
        return view('client_portal.tasks.create', compact('categories'));
    }
    
    /**
     * Store New Task (Safe against duplicates)
     */
    public function storeTask(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:task_categories,id',
            'description' => 'required',
            'priority'    => 'required',
            'due_date'    => 'nullable|date|after:today',
            'attachment'  => 'nullable|file|max:5120|mimes:pdf,jpg,png,docx'
        ]);

        DB::beginTransaction();
        try {
            // 1. Create with Placeholder to get the ID
            // Using uniqid() ensures no collision on the temporary string
            $task = \App\Models\Task::create([
                'task_number'      => 'TEMP-' . uniqid(),
                'client_id'        => auth()->user()->client->id,
                'task_category_id' => $request->category_id,
                'description'      => $request->description,
                'priority'         => $request->priority,
                'due_date'         => $request->due_date,
                'status'           => 'Pending',
                'created_by'       => auth()->id(),
                'assigned_to'      => null
            ]);

            // 2. Generate Number based on the Database ID
            // Format: TSK-MM-YYYY-0000 (e.g., TSK-12-2025-0045)
            $taskNumber = 'TSK-' . date('m') . '-' . date('Y') . '-' . str_pad($task->id, 4, '0', STR_PAD_LEFT);

            // 3. Update the record
            $task->update([
                'task_number' => $taskNumber
            ]);

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('task-attachments', 'public');
                // Logic for saving attachment if needed
            }

            DB::commit();
            return redirect()->route('client.tasks.index')->with('success', 'Request submitted successfully: ' . $taskNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating task: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show Task Details
     */
    public function showTask(Task $task)
    {
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

    // ==========================================
    // Client Documents
    // ==========================================
    public function documents()
    {
        $client = Auth::user()->client;

        if (!$client) {
             return view('client_portal.documents.index', [
                'documents' => collect([]), 
                'error' => 'Client profile not linked.'
            ]);
        }

        $documents = ClientDocument::where('client_id', $client->id)
                                    ->latest()
                                    ->get();

        return view('client_portal.documents.index', compact('documents'));
    }
}