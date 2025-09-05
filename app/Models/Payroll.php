<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'business_id',
        'salary_sheet_id',
        'payment_date',
        'total_amount',
        'status',
        'notes',
    ];

    /**
     * Get the salary sheet associated with the payroll run.
     */
    public function salarySheet(): BelongsTo
    {
        return $this->belongsTo(SalarySheet::class);
    }
}