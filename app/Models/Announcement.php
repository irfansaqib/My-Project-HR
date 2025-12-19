<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to fetch only currently active and valid scheduled announcements.
     * Logic: Active AND (Start Date has passed OR is null) AND (End Date is future OR is null)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('start_date')
                           ->orWhere('start_date', '<=', now());
                     })
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now());
                     })
                     ->orderBy('created_at', 'desc');
    }
}