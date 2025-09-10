<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }



    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }

    /**
     * Get the leave types for the business.
     */
    public function leaveTypes()
    {
        return $this->hasMany(LeaveType::class);
    }

    /**
     * Get the bank accounts for the business.
     */
    public function bankAccounts()
    {
        return $this->hasMany(BusinessBankAccount::class);
    }

    /**
     * Get the email configuration for the business.
     */
    public function emailConfiguration()
    {
        return $this->hasOne(EmailConfiguration::class);
    }
}