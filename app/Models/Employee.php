<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function salaryComponents(): BelongsToMany
    {
        return $this->belongsToMany(SalaryComponent::class, 'employee_salary_component')
                    ->withPivot('amount')
                    ->withTimestamps();
    }
    
    public function qualifications()
    {
        return $this->hasMany(Qualification::class);
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class);
    }

    public function payingBankAccount()
    {
        return $this->belongsTo(BusinessBankAccount::class, 'business_bank_account_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * The leave types assigned to the employee.
     */
    public function leaveTypes(): BelongsToMany
    {
        return $this->belongsToMany(LeaveType::class, 'employee_leave_type')
                    ->withPivot('days_allotted')
                    ->withTimestamps();
    }

    /**
     * Get the attendance records for the employee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}