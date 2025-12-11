<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxClientSalarySheet extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'month' => 'date',
    ];
    
    /**
     * Relationship: Belongs to a Tax Client (Company)
     */
    public function client()
    {
        return $this->belongsTo(TaxClient::class, 'tax_client_id');
    }

    /**
     * Relationship: Has many Salary Items (Rows in the sheet)
     */
    public function items()
    {
        return $this->hasMany(TaxClientSalaryItem::class, 'salary_sheet_id');
    }

    /**
     * The "booted" method of the model.
     * Handles automatic cleanup (Cascading Delete) when a Sheet is deleted.
     */
    protected static function booted()
    {
        static::deleting(function ($sheet) {
            // Automatically delete all items belonging to this sheet
            $sheet->items()->delete(); 
        });
    }
}