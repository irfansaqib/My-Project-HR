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

    /*
    |--------------------------------------------------------------------------
    | Safe Designation & Department Relations
    |--------------------------------------------------------------------------
    | Uses withoutGlobalScopes() to ensure visibility across businesses and
    | cross-module compatibility (prevents "N/A" and RelationNotFound errors).
    */
    public function designationRelation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id')
                    ->withoutGlobalScopes();
    }

    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id')
                    ->withoutGlobalScopes();
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy Aliases (for backward compatibility)
    |--------------------------------------------------------------------------
    | These ensure older code using `$employee->designation` or `$employee->department`
    | keeps working safely without breaking or causing scope issues.
    */
    public function designation(): BelongsTo
    {
        return $this->designationRelation();
    }

    public function department(): BelongsTo
    {
        return $this->departmentRelation();
    }

    /*
    |--------------------------------------------------------------------------
    | Core Business Relationship
    |--------------------------------------------------------------------------
    | Added to resolve RelationNotFoundException:
    | Allows accessing $employee->business safely in controllers (e.g., TaxCalculatorController).
    */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Other Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function salaryComponents(): BelongsToMany
    {
        // ✅ *** THE FIX IS HERE ***
        // Removed 'start_date' and 'end_date' to match your database schema
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

    public function warnings()
    {
        return $this->hasMany(Warning::class)->orderBy('warning_date', 'desc');
    }

    public function incentives()
    {
        return $this->hasMany(Incentive::class)->orderBy('effective_date', 'desc');
    }

    public function salarySheetItems()
    {
        return $this->hasMany(SalarySheetItem::class);
    }

    public function salaryStructures()
    {
        return $this->hasMany(SalaryStructure::class, 'employee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Utility Methods
    |--------------------------------------------------------------------------
    */
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