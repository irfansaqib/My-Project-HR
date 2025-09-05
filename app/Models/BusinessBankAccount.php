<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessBankAccount extends Model
{
    use HasFactory;
    protected $fillable = ['business_id', 'bank_name', 'account_title', 'account_number', 'branch_code', 'is_default'];
}