<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LeaveType extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'business_id',
        'name',
        // ✅ New Policy Fields
        'is_encashable',
        'encashment_variable', // basic_salary or gross_salary
        'encashment_divisor',  // 30 or 26 usually
        'min_balance_required',
        'max_days_encashable',
    ];

    protected $casts = [
        'is_encashable' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_leave_type')
                    ->withPivot('days_allotted')
                    ->withTimestamps();
    }
}