<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalarySheet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
}