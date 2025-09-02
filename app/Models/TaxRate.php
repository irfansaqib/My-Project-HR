<?php

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory, BelongsToBusiness;
    
    protected $guarded = [];

    protected $casts = [
        'slabs' => 'array',
        'effective_from_date' => 'date',
        'effective_to_date' => 'date',
    ];
}