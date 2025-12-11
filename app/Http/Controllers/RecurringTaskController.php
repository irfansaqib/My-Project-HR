<?php

namespace App\Http\Controllers;

use App\Models\RecurringTask;
use App\Models\Client;
use App\Models\Employee;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringTaskController extends Controller
{
    public function index()
    {
        $profiles = RecurringTask::with(['client', 'category', 'assignedEmployee'])->paginate(10);
        return view('recurring_tasks.index', compact('profiles'));
    }

    public function create()
    {
        $clients = Client::where('status', 'active')->get();
        $employees = Employee::where('status', 'active')->get();
        $categories = TaskCategory::where('level', 0)->with('children.children')->get();
        return view('recurring_tasks.create', compact('clients', 'employees', 'categories'));
    }

    public function store(Request $request)
    {
        // Validation is complex because fields depend on Frequency
        $request->validate([
            'client_id' => 'required',
            'category_id' => 'required',
            'assigned_to' => 'required',
            'frequency' => 'required',
            // Conditional rules can be added, but minimal required here
        ]);

        RecurringTask::create([
            'client_id' => $request->client_id,
            'task_category_id' => $request->category_id,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
            'description' => $request->description,
            'priority' => $request->priority,
            'frequency' => $request->frequency,
            // Map inputs
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'day_of_week' => $request->day_of_week,
            'duration_days' => $request->duration_days,
            'reference_start_date' => $request->reference_start_date,
            'month_start_day' => $request->month_start_day,
            'month_end_day' => $request->month_end_day,
            'annual_start_date' => $request->annual_start_date,
            'annual_end_date' => $request->annual_end_date,
        ]);

        return redirect()->route('recurring-tasks.index')->with('success', 'Recurring Profile Saved.');
    }
    public function edit(RecurringTask $recurringTask)
    {
        $clients = Client::where('status', 'active')->get();
        $employees = Employee::where('status', 'active')->get();
        $categories = TaskCategory::where('level', 0)->with('children.children')->get();

        return view('recurring_tasks.edit', compact('recurringTask', 'clients', 'employees', 'categories'));
    }

    public function update(Request $request, RecurringTask $recurringTask)
    {
        // Reuse validation logic or define specific rules
        $request->validate(['frequency' => 'required', 'client_id' => 'required']);

        $recurringTask->update([
            'client_id' => $request->client_id,
            'task_category_id' => $request->category_id, // Ensure this matches form input name
            'assigned_to' => $request->assigned_to,
            'description' => $request->description,
            'priority' => $request->priority,
            'frequency' => $request->frequency,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'day_of_week' => $request->day_of_week,
            'duration_days' => $request->duration_days,
            'reference_start_date' => $request->reference_start_date,
            'month_start_day' => $request->month_start_day,
            'month_end_day' => $request->month_end_day,
            'annual_start_date' => $request->annual_start_date,
            'annual_end_date' => $request->annual_end_date,
            'status' => $request->status, // Active/Inactive
        ]);

        return redirect()->route('recurring-tasks.index')->with('success', 'Recurring Profile Updated.');
    }
    
    public function destroy(RecurringTask $recurringTask)
    {
        $recurringTask->delete();
        return back()->with('success', 'Profile Deleted.');
    }
}