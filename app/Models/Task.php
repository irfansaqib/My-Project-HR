<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    // ===========================
    // 🔗 RELATIONSHIPS
    // ===========================

    /**
     * The Client this task belongs to.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The Category (Service) of the task.
     * Maps to the 'task_category_id' column.
     */
    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    /**
     * The Employee assigned to work on this task.
     * Maps to 'assigned_to'.
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    /**
     * The User who created the task (could be Admin or Client).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Chat messages associated with this task.
     */
    public function messages()
    {
        return $this->hasMany(ClientMessage::class)->orderBy('created_at', 'asc');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    // Check if the current user has an active timer running on this task
    public function activeTimer()
    {
        return $this->hasOne(TaskTimeLog::class)
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->whereNull('stopped_at')
                    ->latest();
    }

    // Calculate total time spent in minutes
    public function totalTimeSpent()
    {
        return $this->timeLogs()->sum('duration_minutes');
    }
    
    // Format duration nicely (e.g., "2h 15m")
    public function formattedTotalTime()
    {
        $minutes = $this->totalTimeSpent();
        $h = floor($minutes / 60);
        $m = $minutes % 60;
        return "{$h}h {$m}m";
    }

    // ===========================
    // 🛠️ HELPER METHODS
    // ===========================

    /**
     * Check if the task is overdue.
     */
    public function isOverdue()
    {
        // If not completed/closed AND due date has passed
        return !in_array($this->status, ['Completed', 'Closed']) 
            && $this->due_date 
            && $this->due_date < now();
    }
}