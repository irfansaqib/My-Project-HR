<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskExtension extends Model
{
    protected $guarded = [];
    protected $casts = [
        'old_due_date' => 'datetime',
        'new_due_date' => 'datetime',
    ];

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}