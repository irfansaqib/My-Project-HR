<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relationship: A message belongs to a Task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Relationship: A message is sent by a User
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

}