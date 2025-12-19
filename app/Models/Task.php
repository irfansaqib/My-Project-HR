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
        'completed_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    // ===========================
    // 🔗 RELATIONSHIPS
    // ===========================

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        // We explicitly tell Laravel: "The column in the 'tasks' table is task_category_id"
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // ✅ MESSAGES (Chat History)
    public function messages()
    {
        return $this->hasMany(TaskMessage::class)->orderBy('created_at', 'asc');
    }

    // ✅ DOCUMENTS (Attachments)
    // This tells the Task to look for files in the 'client_documents' table
    public function documents()
    {
        return $this->hasMany(ClientDocument::class, 'task_id');
    }

    // ✅ EXTENSIONS (Due Date History)
    public function extensions()
    {
        return $this->hasMany(TaskExtension::class)->orderBy('created_at', 'desc');
    }

    // ✅ TIMERS (Time Tracking)
    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function activeTimer()
    {
        return $this->hasOne(TaskTimeLog::class)->whereNull('stopped_at')->latest();
    }

    // ===========================
    // 🛠️ HELPER METHODS
    // ===========================

    /**
     * Check if the task is Overdue.
     * Logic: Due date passed AND status is not Completed/Closed.
     */
    public function isOverdue()
    {
        if (in_array($this->status, ['Completed', 'Closed'])) {
            return false;
        }
        
        return $this->due_date && $this->due_date < now();
    }

    /**
     * Count how many times the due date was extended.
     */
    public function extensionCount()
    {
        return $this->extensions()->count();
    }

    /**
     * Calculate total time spent in "1h 30m" format.
     */
    public function formattedTotalTime()
    {
        $totalMinutes = $this->timeLogs->sum(function($log) {
            $end = $log->stopped_at ?? now();
            return $log->started_at->diffInMinutes($end);
        });

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }
}