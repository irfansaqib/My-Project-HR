<?php

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Employee extends Model
{
    use HasFactory, BelongsToBusiness;

    /**
     * We are using guarded which means all fields are fillable.
     * This is simpler for your large forms.
     */
    protected $guarded = [];

    /**
     * The relationships that should always be loaded with the employee.
     */
    protected $with = ['qualifications', 'experiences'];

    /**
     * Accessor to automatically calculate the total salary.
     */
    protected function totalSalary(): Attribute
    {
        return Attribute::make(
            get: fn () => 
                ($this->basic_salary ?? 0) + 
                ($this->house_rent ?? 0) + 
                ($this->utilities ?? 0) + 
                ($this->medical ?? 0) + 
                ($this->conveyance ?? 0) + 
                ($this->other_allowance ?? 0),
        );
    }

    /**
     * Accessor to automatically calculate the total leaves.
     */
    protected function totalLeaves(): Attribute
    {
        return Attribute::make(
            get: fn () => 
                ($this->leaves_sick ?? 0) + 
                ($this->leaves_casual ?? 0) + 
                ($this->leaves_annual ?? 0) + 
                ($this->leaves_other ?? 0),
        );
    }

    // --- RELATIONSHIPS ---
    public function qualifications()
    {
        return $this->hasMany(Qualification::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Get all of the leave requests for the employee.
     * THIS IS THE MISSING METHOD THAT FIXES THE ERROR.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}