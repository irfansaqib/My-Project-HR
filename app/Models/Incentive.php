<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * This 'casts' array correctly treats the 'effective_date' as a date object.
     */
    protected $casts = [
        'effective_date' => 'date',
    ];

    /**
     * Defines the relationship that an incentive belongs to an employee.
     */
    public function employee()
    {
        // ✅ DEFINITIVE FIX: Replaced the period '.' with a double-colon '::'
        return $this->belongsTo(Employee::class);
    }
}