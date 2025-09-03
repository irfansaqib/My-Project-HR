<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySheetItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'salary_sheet_id',
        'employee_id',
        'gross_salary',
        'deductions',
        'income_tax',
        'net_salary',
    ];

    /**
     * Get the employee that this item belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the salary sheet that this item belongs to.
     */
    public function salarySheet(): BelongsTo
    {
        return $this->belongsTo(SalarySheet::class);
    }
}