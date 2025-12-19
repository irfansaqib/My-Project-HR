<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    use HasFactory;

    protected $table = 'client_documents';

    protected $fillable = [
        'client_id',
        'task_id',    // <--- ADDED THIS so we can assign documents to tasks
        'title',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    // Relationship: A document belongs to one Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relationship: A document belongs to one Task
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}