<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    // ✅ UPDATED: Explicitly list all allowed fields to prevent "Mass Assignment" errors
    protected $fillable = [
        'business_id',
        'user_id',
        'business_name',
        'business_type',        // New Field
        'cnic',                 // New Field
        'registration_number',  // New Field
        'ntn',                  // New Field
        'ntn_cnic',             // Kept for backward compatibility if needed
        'id_type',              // Kept for backward compatibility if needed
        'contact_person',
        'phone',
        'email',
        'industry',
        'address',
        'default_employee_id',
        'status'
    ];

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
    
    public function defaultEmployee()
    {
        return $this->belongsTo(Employee::class, 'default_employee_id');
    }
}