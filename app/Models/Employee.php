<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'employee_number', 'photo_path', 'attachment_path', 'name', 
        'father_name', 'cnic', 'dob', 'gender', 'phone', 'email', 'address',
        'emergency_contact_name', 'emergency_contact_relation', 'emergency_contact_phone',
        'designation', 'department', 'joining_date', 'status',
        'basic_salary', 'house_rent', 'utilities', 'medical', 'conveyance', 'other_allowance',
        'leaves_sick', 'leaves_casual', 'leaves_annual', 'leaves_other',
        'leave_period_from', 'leave_period_to',
    ];

    /**
     * Get the employee's total salary by summing the components.
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
     * Get the employee's total allocated leaves.
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

    public function qualifications(): HasMany
    {
        return $this->hasMany(Qualification::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }
}