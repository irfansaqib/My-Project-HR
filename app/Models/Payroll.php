<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function salarySheet()
    {
        return $this->belongsTo(SalarySheet::class);
    }

    /**
     * The salary sheet items that belong to this payroll run.
     */
    public function items()
    {
        return $this->belongsToMany(SalarySheetItem::class);
    }
}