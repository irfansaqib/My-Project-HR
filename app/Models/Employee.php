<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

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

    public function leaveTypes(): BelongsToMany
    {
        return $this->belongsToMany(LeaveType::class, 'employee_leave_type')
                    ->withPivot('days_allotted')
                    ->withTimestamps();
    }

    public function shiftAssignments()
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * ✅ NEW: Relationship to the warnings table.
     */
    public function warnings()
    {
        return $this->hasMany(Warning::class)->orderBy('warning_date', 'desc');
    }

    public function getActiveShiftForDate(Carbon $date)
    {
        $assignment = $this->shiftAssignments()
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->where('end_date', '>=', $date->format('Y-m-d'))
                      ->orWhereNull('end_date');
            })
            ->latest('start_date')
            ->first();

        return $assignment ? $assignment->shift : null;
    }
}