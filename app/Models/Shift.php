<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'punch_in_window_start' => 'datetime:H:i',
        'punch_in_window_end' => 'datetime:H:i',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_shift_assignments')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }
}