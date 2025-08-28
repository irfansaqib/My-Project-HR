<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // This is the owner's ID
        'legal_name',
        'business_name',
        'business_type',
        'ntn_number',
        'registration_number',
        'address',
        'phone_number',
        'email',
        'logo_path',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}