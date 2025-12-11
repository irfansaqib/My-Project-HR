<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxClientEmployee extends Model
{
    protected $guarded = []; // Should be empty to allow mass assignment

    protected $casts = [
        'current_allowances' => 'array',
        'joining_date' => 'date',
        'exit_date' => 'date',
    ];
    
    public function client() { return $this->belongsTo(TaxClient::class, 'tax_client_id'); }
    public function salaryItems() { return $this->hasMany(TaxClientSalaryItem::class); }
}