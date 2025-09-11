<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'check_in',
        'check_out',
    ];

    /**
     * --- FINAL FIX ---
     * This tells Laravel to automatically convert the 'date' column
     * from a text string into a proper date object (Carbon instance).
     * This allows you to use functions like ->format() on it.
     */
    protected $casts = [
        'date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}