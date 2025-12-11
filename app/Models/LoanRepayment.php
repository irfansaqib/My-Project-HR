<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function salarySheetItem()
    {
        return $this->belongsTo(SalarySheetItem::class);
    }

    /**
     * The "booted" method of the model.
     * Automatically updates the parent Loan's recovered amount when a repayment is made.
     */
    protected static function booted()
    {
        static::saved(function ($repayment) {
            $repayment->updateLoanRecovery();
        });

        static::deleted(function ($repayment) {
            $repayment->updateLoanRecovery();
        });
    }

    public function updateLoanRecovery()
    {
        $loan = $this->loan;
        if ($loan) {
            // Calculate total repaid from history
            $totalRecovered = $loan->repayments()->sum('amount');
            
            // Update the loan record
            $loan->update([
                'recovered_amount' => $totalRecovered,
                // Auto-complete if fully paid
                'status' => ($totalRecovered >= $loan->total_amount) ? 'completed' : $loan->status
            ]);
        }
    }
}