<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LeaveType extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * The employees that are assigned this leave type.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_leave_type')
                    ->withPivot('days_allotted')
                    ->withTimestamps();
    }
}