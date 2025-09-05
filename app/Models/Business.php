<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'legal_name',
        'address',
        'phone_number',
        'email',
        'logo_path',
        'registration_number',
        'ntn_number',
        'business_type',
    ];

    /**
     * Get the users associated with the business.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * THIS IS THE NEW METHOD
     * Get the bank accounts for the business.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BusinessBankAccount::class);
    }
}