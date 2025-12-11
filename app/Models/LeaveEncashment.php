<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEncashment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'encashment_date' => 'date',
        'amount' => 'decimal:2',
        'days' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function salarySheetItem(): BelongsTo
    {
        return $this->belongsTo(SalarySheetItem::class);
    }
}