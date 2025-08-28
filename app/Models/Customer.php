<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'customer_id',
        'name',
        'type',
        'contact_person',
        'email',
        'phone',
        'address',
        'ntn',
        'cnic',
        'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}