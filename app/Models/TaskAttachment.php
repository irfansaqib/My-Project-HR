<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    use HasFactory;

    // Defines the table name explicitly
    protected $table = 'task_attachments';

    protected $guarded = [];

    // Relationship back to the Task
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}