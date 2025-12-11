<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    protected $casts = [
        'repayment_start_date' => 'date',
        'loan_date' => 'date',
        'total_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'recovered_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class)->orderBy('payment_date', 'desc');
    }
    
    public function getPendingAmountAttribute()
    {
        return max(0, $this->total_amount - $this->recovered_amount);
    }
}