<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundContribution extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}