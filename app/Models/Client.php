<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $guarded = [];

    // The Login User (Client Portal Access)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // The Business this client belongs to (Your Agency)
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Assignments (Which employee handles what)
    public function assignments()
    {
        return $this->hasMany(ClientAssignment::class);
    }

    // Tasks linked to this client
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}