<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LoanRepayment;
use App\Models\FundContribution;
use App\Models\LeaveEncashment;

class SalarySheet extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'month' => 'date',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function items()
    {
        return $this->hasMany(SalarySheetItem::class);
    }

    /**
     * The "booted" method handles automatic cleanup on deletion.
     */
    protected static function booted()
    {
        static::deleting(function ($salarySheet) {
            // Load items to iterate through them
            $salarySheet->load('items');
            
            foreach ($salarySheet->items as $item) {
                
                // 1. REVERSE Leave Encashments (Set back to 'Approved' & Unlink)
                LeaveEncashment::where('salary_sheet_item_id', $item->id)->update([
                    'status' => 'approved', // Revert to approved so it can be paid in next sheet
                    'salary_sheet_item_id' => null,
                    'updated_at' => now(),
                ]);

                // 2. DELETE Loan Repayments
                // We use get()->each->delete() so that LoanRepayment model events fire 
                // and update the main Loan balance automatically.
                LoanRepayment::where('salary_sheet_item_id', $item->id)->get()->each(function($repayment) {
                    $repayment->delete();
                });
                
                // 3. DELETE Fund Contributions
                FundContribution::where('salary_sheet_item_id', $item->id)->delete();
                
                // 4. DELETE the Item itself
                $item->delete();
            }
        });
    }
}