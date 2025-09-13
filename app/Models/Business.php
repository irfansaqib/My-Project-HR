<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Import HasOne

class Business extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'business_name',
        'legal_name',
        'registration_number',
        'ntn_number',
        'phone_number',
        'email',
        'business_type',
        'address',
        'logo_path',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
    
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }
    
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
    
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BusinessBankAccount::class);
    }

    /**
     * Get the email configuration associated with the business.
     * This is the only new code added to fix the error.
     */
    public function emailConfiguration(): HasOne
    {
        return $this->hasOne(EmailConfiguration::class);
    }
}

