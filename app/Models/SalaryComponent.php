<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalaryComponent extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'business_id',
        'name',
        'type',
        'is_tax_exempt',
        'exemption_type',
        'exemption_value',
        // New fields
        'is_advance',
        'is_loan',
        'is_contributory', // ✅ ADDED
        'is_tax_component',
    ];

    protected $casts = [
        'is_tax_exempt' => 'boolean',
        'is_advance' => 'boolean',
        'is_loan' => 'boolean',
        'is_contributory' => 'boolean',
        'is_tax_component' => 'boolean',
    ];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_salary_component')
                    ->withPivot('amount')
                    ->withTimestamps();
    }
}