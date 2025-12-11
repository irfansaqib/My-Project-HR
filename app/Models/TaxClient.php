<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxClient extends Model
{
    protected $guarded = [];

    // ✅ CASTS: Automatically convert JSON to Array
    protected $casts = [
        'is_onboarded' => 'boolean',
        'payroll_start_month' => 'date',
        'saved_salary_months' => 'array', // Critical for the tracking logic
    ];
    
    public function employees() { return $this->hasMany(TaxClientEmployee::class); }
    public function salarySheets() { return $this->hasMany(TaxClientSalarySheet::class); }
    public function components() { return $this->hasMany(TaxClientSalaryComponent::class, 'tax_client_id'); }
}