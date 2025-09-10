<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySheetItem extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    public function salarySheet()
    {
        return $this->belongsTo(SalarySheet::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * The payroll runs that this item belongs to.
     */
    public function payrolls()
    {
        return $this->belongsToMany(Payroll::class);
    }
}