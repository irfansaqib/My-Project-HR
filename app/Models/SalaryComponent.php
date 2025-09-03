<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalaryComponent extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'name',
        'type',
        'is_tax_exempt',
        'exemption_type',
        'exemption_value',
    ];

    /**
     * The employees that have this salary component.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_salary_component')
                    ->withPivot('amount') // Important: allows us to access the specific amount
                    ->withTimestamps();
    }
}