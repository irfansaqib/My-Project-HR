<?php

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $guarded = [];
    protected $with = ['qualifications', 'experiences', 'salaryComponents'];

    /**
     * The salary components attached to this employee.
     */
    public function salaryComponents()
    {
        return $this->belongsToMany(SalaryComponent::class, 'employee_salary')->withPivot('amount');
    }
    
    /**
     * Accessor for total allowances (excluding basic salary).
     */
    protected function totalAllowances(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->salaryComponents->where('type', 'allowance')->sum('pivot.amount')
        );
    }
    
    /**
     * Accessor for total deductions.
     */
    protected function totalDeductions(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->salaryComponents->where('type', 'deduction')->sum('pivot.amount')
        );
    }

    // --- RELATIONSHIPS ---
    public function qualifications() { return $this->hasMany(Qualification::class); }
    public function experiences() { return $this->hasMany(Experience::class); }
    public function user() { return $this->hasOne(User::class); }
    public function leaveRequests() { return $this->hasMany(LeaveRequest::class); }

    // This method is no longer needed as leaves are not stored on the employee table
    // but calculated in the LeaveRequestController.
}