<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalarySheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'month',
        'status',
    ];

    /**
     * Get the items for the salary sheet.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalarySheetItem::class);
    }

    /**
     * Get the business that this salary sheet belongs to.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}