<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftAssignment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'employee_shift_assignments';

    /**
     * Get the shift associated with the assignment.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the employee associated with the assignment.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}