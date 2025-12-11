<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringTask extends Model
{
    protected $guarded = [];

    // Cast dates properly
    protected $casts = [
        'reference_start_date' => 'date',
        'annual_start_date' => 'date',
        'annual_end_date' => 'date',
        'last_run_at' => 'date',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function category() { return $this->belongsTo(TaskCategory::class, 'task_category_id'); }
    public function assignedEmployee() { return $this->belongsTo(Employee::class, 'assigned_to'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}