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

    public function leaveTypes()
    {
        return $this->hasMany(LeaveType::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BusinessBankAccount::class);
    }
    
    public function emailConfiguration()
    {
        return $this->hasOne(EmailConfiguration::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Get the holidays for the business.
     */
    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }
}