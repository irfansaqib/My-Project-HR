<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailConfiguration extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     * An empty guarded array means all attributes are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}